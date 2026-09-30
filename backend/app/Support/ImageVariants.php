<?php

namespace App\Support;

/**
 * Pembaca manifest varian WebP yang ditulis tools/optimize_images.py.
 *
 * Komponen <x-img> hanya boleh menyebut varian yang benar-benar ada di disk,
 * jadi sumber kebenarannya adalah manifest, bukan pola nama file yang ditebak.
 * Gambar yang belum dikonversi tidak ada di manifest, dan semua metode di sini
 * jatuh kembali ke URL aslinya tanpa error.
 */
class ImageVariants
{
    private static ?array $manifest = null;

    /**
     * @return array{width:int,height:int,bytes:int,variants:array<int,array{width:int,path:string,bytes:int}>}|null
     */
    public static function for(string $src): ?array
    {
        // Kunci manifest adalah URL publik ("/assets/img/...", "/content-images/...").
        // Query string dibuang supaya cache-buster tidak membuat lookup gagal.
        $path = parse_url($src, PHP_URL_PATH) ?: $src;

        return static::manifest()[$path] ?? null;
    }

    /**
     * srcset siap pakai, terurut dari lebar terkecil.
     */
    public static function srcset(array $entry): string
    {
        $variants = $entry['variants'];
        usort($variants, fn (array $a, array $b) => $a['width'] <=> $b['width']);

        return implode(', ', array_map(
            fn (array $v) => "{$v['path']} {$v['width']}w",
            $variants,
        ));
    }

    /**
     * URL varian terkecil yang masih >= $width, untuk tempat yang hanya bisa
     * menerima satu URL. og:image misalnya, di mana berkas asli 6,9 MB tidak
     * akan pernah ditampilkan sebagai pratinjau oleh platform mana pun.
     */
    public static function best(string $src, int $width): string
    {
        $entry = static::for($src);
        if (! $entry) {
            return $src;
        }

        $variants = $entry['variants'];
        usort($variants, fn (array $a, array $b) => $a['width'] <=> $b['width']);

        foreach ($variants as $variant) {
            if ($variant['width'] >= $width) {
                return $variant['path'];
            }
        }

        return end($variants)['path'];
    }

    /**
     * Bungkus setiap <img> di dalam HTML lepas (isi artikel hasil crawl) dengan
     * <picture> + sumber WebP.
     *
     * Dilakukan di sini, bukan saat impor, supaya rekaman di database tetap
     * sama dengan hasil crawl: impor ulang tidak perlu membatalkan penulisan
     * ulang sebelumnya.
     */
    public static function enhance(string $html, string $sizes = '100vw'): string
    {
        return preg_replace_callback(
            '/<img\b[^>]*\bsrc="([^"]+)"[^>]*>/i',
            function (array $m) use ($sizes) {
                $entry = static::for($m[1]);
                if (! $entry) {
                    return $m[0];
                }

                return '<picture style="display:contents">'
                    .'<source type="image/webp" srcset="'.e(static::srcset($entry)).'" sizes="'.e($sizes).'">'
                    .$m[0]
                    .'</picture>';
            },
            $html,
        );
    }

    /**
     * Manifest yang ditulis saat build, untuk gambar tema situs.
     *
     * Hidup di root repo, bukan di dalam backend/: satu berkas melayani dua
     * tree gambar, dan salah satunya (content-migration/) milik tooling
     * migrasi, bukan aplikasi Laravel. Ikut di dalam image dan hanya dibaca.
     */
    public static function jalurManifestBuild(): string
    {
        return base_path('../data/image-variants.json');
    }

    /**
     * Manifest yang ditulis saat gambar diunggah orang.
     *
     * Berkas terpisah, dan pemisahan itu bukan kerapian.
     *
     * Manifest build ikut di dalam image Docker dan berganti tiap build.
     * Manifest unggahan harus bertahan melewati build, jadi ia hidup di volume
     * — dan volume menang selamanya atas isi image. Menaruh keduanya dalam satu
     * berkas berarti memilih salah satu yang rusak: kalau berkasnya di image,
     * entri unggahan hilang tiap deploy; kalau di volume, entri build tidak
     * pernah diperbarui.
     *
     * Itu persis yang terjadi pada 30 September 2026: manifest di container
     * tertinggal 224 entri sementara repo punya 226, dan poster pengumuman
     * dilayani sebagai PNG 1,54 MB padahal varian WebP 207 KB-nya ada di disk.
     */
    public static function jalurManifestUnggahan(): string
    {
        return base_path('../content-migration/images-opt/manifest-unggahan.json');
    }

    /** Menulis atau memperbarui satu entri pada manifest unggahan. */
    public static function catat(string $url, array $entri): void
    {
        $path = static::jalurManifestUnggahan();

        @mkdir(dirname($path), 0775, true);

        $isi = is_file($path)
            ? (json_decode((string) file_get_contents($path), true) ?: [])
            : [];

        $isi[$url] = $entri;

        // Ditulis lewat berkas sementara lalu dipindahkan: dua permintaan yang
        // menyimpan gambar bersamaan dapat menimpa berkas di tengah penulisan,
        // dan manifest yang rusak setengah jalan membuat setiap gambar di situs
        // jatuh ke berkas aslinya sekaligus.
        $tmp = $path.'.'.getmypid().'.tmp';
        file_put_contents($tmp, json_encode($isi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        rename($tmp, $path);

        static::$manifest = null;
    }

    private static function manifest(): array
    {
        if (static::$manifest === null) {
            $baca = function (string $path): array {
                return is_file($path)
                    ? (json_decode((string) file_get_contents($path), true) ?: [])
                    : [];
            };

            // Unggahan menimpa build bila kuncinya sama: yang lebih baru
            // adalah yang baru saja ditulis orang.
            static::$manifest = array_merge(
                $baca(static::jalurManifestBuild()),
                $baca(static::jalurManifestUnggahan()),
            );
        }

        return static::$manifest;
    }
}
