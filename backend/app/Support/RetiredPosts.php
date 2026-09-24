<?php

namespace App\Support;

/**
 * URL artikel yang sudah terindeks Google tapi kini tidak punya halaman.
 *
 * 26 dari 61 artikel hasil crawl dihapus karena tidak punya tanggal terbit, dan
 * URL-nya tetap hidup di indeks Google. `she-activity` bahkan kehilangan
 * seluruh 8 artikelnya. Dibiarkan begitu, cutover menghasilkan 26 crawl error
 * sekaligus dan pengunjung dari hasil pencarian mendarat di halaman error.
 *
 * Jawabannya **410 Gone**, diputuskan 2026-09-23: artikel-artikel itu dihapus
 * permanen, bukan menunggu tanggalnya dikonfirmasi. 410 memberi tahu Google
 * bahwa URL-nya memang sudah tidak ada, dan URL yang dijawab 410 dikeluarkan
 * dari indeks lebih cepat ketimbang 404, yang dianggap mungkin sementara.
 *
 * Yang sengaja tidak dilakukan: mengalihkan ke daftar kategori. Pengalihan ke
 * halaman yang bukan pengganti isinya dinilai Google sebagai soft 404, dan
 * untuk she-activity pengalihan itu bahkan bermuara ke daftar kosong.
 *
 * Berkas hasil crawl di content-migration/raw/ tidak ikut dihapus, itu rekaman
 * situs lama, dan importer sekarang melewati artikel tanpa tanggal supaya
 * mereka tidak hidup lagi diam-diam.
 */
class RetiredPosts
{
    private static ?array $slugs = null;

    /**
     * Kategori tempat slug ini dulu tinggal, atau null kalau URL ini memang
     * tidak pernah ada di situs lama.
     */
    public static function categoryFor(string $slug): ?string
    {
        return static::slugs()[$slug] ?? null;
    }

    /** @return array<string,string> slug => kategori */
    private static function slugs(): array
    {
        if (static::$slugs === null) {
            $path = base_path('../data/discovered-urls.json');
            $discovered = is_file($path)
                ? json_decode((string) file_get_contents($path), true) ?: []
                : [];

            static::$slugs = [];
            foreach ($discovered as $row) {
                $slug = basename((string) parse_url($row['url'], PHP_URL_PATH));
                static::$slugs[$slug] = $row['category'];
            }
        }

        return static::$slugs;
    }
}
