<?php

namespace Tests\Feature;

use App\Support\ImageVariants;
use Tests\TestCase;

class ImageVariantsTest extends TestCase
{
    /**
     * Manifest menyimpan URL, bukan path disk, dan keduanya pernah tertukar
     * sekali: varian gambar artikel sempat ditulis sebagai
     * /content-migration/images-opt/... padahal yang dilayani
     * /content-images-opt/.... Halaman tetap 200 dan hanya gambarnya yang
     * hilang, jadi kekeliruan seperti ini hanya ketangkap dengan mengecek
     * setiap URL di manifest benar-benar punya berkas di public/.
     */
    /**
     * Manifest tidak dilacak git, jadi clone yang belum menjalankan konverter
     * tidak punya berkas ini. Itu keadaan sah, <x-img> memakai gambar asli,
     * bukan kegagalan, jadi tes yang bergantung padanya dilewat.
     */
    private function manifestPath(): string
    {
        $path = base_path('../data/image-variants.json');

        if (! is_file($path)) {
            $this->markTestSkipped('data/image-variants.json belum dibuat, jalankan tools/optimize_images.py.');
        }

        return $path;
    }

    public function test_every_url_in_the_manifest_resolves_to_a_file(): void
    {
        $path = $this->manifestPath();
        $manifest = json_decode((string) file_get_contents($path), true);
        $this->assertNotEmpty($manifest, 'Manifest ada tapi kosong.');

        $missing = [];
        foreach ($manifest as $source => $entry) {
            // Gambar sumber untuk artikel adalah masukan crawler dan sengaja
            // tidak dilacak git, 139 MB resolusi kamera. Yang dilacak justru
            // variannya. Jadi hanya sumber di assets/ yang wajib ada; di clone
            // yang belum menjalankan crawler, /content-images/ memang kosong.
            if (! str_starts_with($source, '/content-images/') && ! is_file(public_path($source))) {
                $missing[] = $source;
            }

            // Varian wajib ada tanpa kecuali: itulah berkas yang dikirim ke
            // browser, dan kekeliruan URL di sini tidak membuat halaman gagal.
            foreach ($entry['variants'] as $variant) {
                if (! is_file(public_path($variant['path']))) {
                    $missing[] = $variant['path'];
                }
            }
        }

        $this->assertSame([], $missing, 'URL di manifest tanpa berkas: '.implode(', ', array_slice($missing, 0, 5)));
    }

    public function test_srcset_is_ordered_from_the_smallest_width(): void
    {
        $srcset = ImageVariants::srcset([
            'variants' => [
                ['width' => 800, 'path' => '/b.webp', 'bytes' => 2],
                ['width' => 400, 'path' => '/a.webp', 'bytes' => 1],
            ],
        ]);

        $this->assertSame('/a.webp 400w, /b.webp 800w', $srcset);
    }

    public function test_unknown_images_fall_back_to_the_original(): void
    {
        $this->assertNull(ImageVariants::for('/assets/img/tidak-ada.jpg'));
        $this->assertSame('/assets/img/tidak-ada.jpg', ImageVariants::best('/assets/img/tidak-ada.jpg', 800));

        $html = '<img src="/assets/img/tidak-ada.jpg" alt="">';
        $this->assertSame($html, ImageVariants::enhance($html));
    }

    public function test_known_images_get_a_webp_source(): void
    {
        $this->manifestPath();

        $html = ImageVariants::enhance('<img src="/assets/img/logo.png" alt="Hasnur Group">');

        $this->assertStringContainsString('<source type="image/webp"', $html);
        $this->assertStringContainsString('.webp 400w', $html);
        // Aslinya tetap jadi fallback: tidak ada browser yang kehilangan gambar.
        $this->assertStringContainsString('src="/assets/img/logo.png"', $html);
    }

    public function test_best_picks_the_smallest_variant_that_is_wide_enough(): void
    {
        $this->manifestPath();

        // logo.png 2072 px lebar, jadi variannya 400/800/1600.
        $this->assertStringContainsString('-800.webp', ImageVariants::best('/assets/img/logo.png', 500));
        $this->assertStringContainsString('-1600.webp', ImageVariants::best('/assets/img/logo.png', 1600));
        // Lebih lebar dari varian terbesar: ambil yang terbesar, jangan gagal.
        $this->assertStringContainsString('-1600.webp', ImageVariants::best('/assets/img/logo.png', 4000));
    }
}
