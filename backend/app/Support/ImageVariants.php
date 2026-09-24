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

    private static function manifest(): array
    {
        if (static::$manifest === null) {
            // Manifest hidup di root repo, bukan di dalam backend/: satu berkas
            // melayani dua tree gambar, dan salah satunya (content-migration/)
            // milik tooling migrasi, bukan aplikasi Laravel.
            $path = base_path('../data/image-variants.json');

            static::$manifest = is_file($path)
                ? json_decode((string) file_get_contents($path), true) ?: []
                : [];
        }

        return static::$manifest;
    }
}
