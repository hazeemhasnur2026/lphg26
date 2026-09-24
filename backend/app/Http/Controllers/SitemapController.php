<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * sitemap.xml dibangun dari rute dan database, bukan disalin tangan.
 *
 * `sitemap.xml` statis di root prototipe hanya memuat 21 halaman tetap dan tak
 * pernah tahu soal artikel, sementara situs lama punya puluhan URL /post/ yang
 * sudah terindeks. Sitemap yang tertinggal dari isinya sendiri adalah cara
 * paling mudah membuat halaman baru tidak ketemu setelah cutover.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        foreach ($this->paths() as $path => $lastmod) {
            // Tiap halaman punya satu URL per bahasa dan semuanya harus
            // diajukan: hreflang di <head> menghubungkan ketiganya, tapi Google
            // hanya menemukan yang benar-benar terdaftar di sini.
            foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale) {
                $urls[] = [
                    // Argumen keempat dibiarkan false: dipaksa true, versi
                    // Indonesia ditulis /id/... padahal URL kanoniknya tanpa
                    // prefiks. Sitemap jadi berisi alamat yang dialihkan.
                    'loc' => LaravelLocalization::getLocalizedURL($locale, url($path)),
                    'lastmod' => $lastmod,
                ];
            }
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /** @return array<string, string|null> path => lastmod */
    private function paths(): array
    {
        $paths = ['/' => null];

        foreach (array_keys(config('site.pages')) as $path) {
            $paths["/page/{$path}"] = null;
        }

        // Hubungi Kami punya rute sendiri, bukan entri di config/site.pages:
        // rute generik /page/{group}/{slug} hanya bisa merender view tanpa data,
        // sedangkan halaman ini butuh alamat kantor dan penanganan POST.
        $paths['/page/contact/contact-us'] = null;

        // Daftar yang tidak memuat satu artikel pun tidak diajukan ke Google:
        // halaman kosong berisiko dinilai soft 404. Halamannya tetap bisa
        // dibuka dan masuk sitemap lagi begitu ada isinya.
        $filled = Post::select('category')->distinct()->pluck('category')->all();

        foreach (array_keys(config('site.listings')) as $category) {
            if (! in_array($category, $filled, true)) {
                continue;
            }

            $section = config("site.listings.{$category}.section");
            $paths["/page/{$section}/{$category}"] = null;
        }

        // Kutipan liputan media tidak punya halaman milik kami. URL-nya
        // menunjuk ke penerbitnya, jadi tidak boleh masuk sitemap kami.
        $posts = Post::whereNull('external_url')
            ->orderByRaw('published_at IS NULL, published_at DESC, id DESC')
            ->get(['category', 'slug', 'published_at', 'updated_at']);

        foreach ($posts as $post) {
            $paths["/post/{$post->category}/{$post->slug}"] =
                ($post->published_at ?? $post->updated_at)?->toDateString();
        }

        return $paths;
    }
}
