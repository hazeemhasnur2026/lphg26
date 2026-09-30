<?php

namespace Tests\Feature;

use App\Support\ImageVariants;
use App\Support\PengoptimalGambar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Varian WebP untuk gambar yang diunggah orang lewat panel.
 *
 * Skrip Python hanya menjangkau gambar yang sudah ada saat build. Gambar
 * unggahan baru ada setelah deploy, dan sebelum ini dilayani pada ukuran
 * aslinya — foto ponsel empat megabita tetap empat megabita ke tiap pengunjung.
 */
class PengoptimalGambarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD tanpa dukungan WebP di lingkungan ini.');
        }
    }

    private function gambarUji(int $lebar, int $tinggi, string $nama): string
    {
        $img = imagecreatetruecolor($lebar, $tinggi);
        imagefill($img, 0, 0, imagecolorallocate($img, 20, 80, 160));
        $path = Storage::disk('content')->path($nama);
        @mkdir(dirname($path), 0775, true);
        imagejpeg($img, $path, 90);
        imagedestroy($img);

        return $nama;
    }

    public function test_variants_are_written_for_an_uploaded_image(): void
    {
        $nama = $this->gambarUji(2000, 1200, 'unggahan/uji-besar.jpg');

        $this->assertTrue(PengoptimalGambar::proses($nama));

        $entri = ImageVariants::for('/content-images/'.$nama);

        $this->assertNotNull($entri, 'Gambar tidak tercatat di manifest.');
        $this->assertSame(2000, $entri['width']);
        // 400, 800, 1600 — tidak ada yang lebih besar daripada aslinya.
        $this->assertSame([400, 800, 1600], array_column($entri['variants'], 'width'));

        foreach ($entri['variants'] as $v) {
            $berkas = base_path('../content-migration/images-opt/'
                .str_replace('/content-images-opt/', '', $v['path']));
            $this->assertFileExists($berkas, "Varian disebut manifest tapi tidak ada: {$v['path']}");
        }
    }

    /** Gambar kecil tidak diperbesar; ia tetap dapat satu varian WebP. */
    public function test_a_small_image_is_never_upscaled(): void
    {
        $nama = $this->gambarUji(300, 200, 'unggahan/uji-kecil.jpg');

        PengoptimalGambar::proses($nama);
        $entri = ImageVariants::for('/content-images/'.$nama);

        $this->assertSame([300], array_column($entri['variants'], 'width'));
    }

    public function test_the_variant_is_smaller_than_the_original(): void
    {
        $nama = $this->gambarUji(1600, 1000, 'unggahan/uji-ukuran.jpg');

        PengoptimalGambar::proses($nama);
        $entri = ImageVariants::for('/content-images/'.$nama);

        $terbesar = end($entri['variants']);

        $this->assertLessThan($entri['bytes'], $terbesar['bytes'],
            'Varian WebP tidak lebih kecil daripada berkas asli.');
    }

    /**
     * Berkas yang bukan gambar, dan gambar yang gagal dibaca, tidak boleh
     * menggagalkan penyimpanan artikel. Gambar yang gagal dioptimalkan tetap
     * tampil memakai berkas aslinya.
     */
    public function test_nothing_throws_on_input_it_cannot_handle(): void
    {
        Storage::disk('content')->put('unggahan/bukan-gambar.pdf', 'bukan gambar');
        Storage::disk('content')->put('unggahan/rusak.jpg', 'ini bukan JPEG sungguhan');

        $this->assertFalse(PengoptimalGambar::proses('unggahan/bukan-gambar.pdf'));
        $this->assertFalse(PengoptimalGambar::proses('unggahan/rusak.jpg'));
        $this->assertFalse(PengoptimalGambar::proses(null));
        $this->assertFalse(PengoptimalGambar::proses('unggahan/tidak-ada.jpg'));
    }

    /**
     * Manifest unggahan terpisah dari manifest build, dan pemisahan itu yang
     * membuat keduanya bisa hidup bersama: yang satu ikut di dalam image dan
     * berganti tiap build, yang lain hidup di volume dan harus bertahan.
     */
    public function test_the_two_manifests_are_separate_files(): void
    {
        $this->assertNotSame(
            ImageVariants::jalurManifestBuild(),
            ImageVariants::jalurManifestUnggahan(),
        );
    }

    protected function tearDown(): void
    {
        foreach (['uji-besar', 'uji-kecil', 'uji-ukuran'] as $n) {
            @unlink(Storage::disk('content')->path("unggahan/{$n}.jpg"));
            foreach ([300, 400, 800, 1600] as $w) {
                @unlink(base_path("../content-migration/images-opt/unggahan/{$n}-{$w}.webp"));
            }
        }
        @unlink(Storage::disk('content')->path('unggahan/bukan-gambar.pdf'));
        @unlink(Storage::disk('content')->path('unggahan/rusak.jpg'));
        @unlink(ImageVariants::jalurManifestUnggahan());

        parent::tearDown();
    }
}
