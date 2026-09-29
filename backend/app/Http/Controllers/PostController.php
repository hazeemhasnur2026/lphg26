<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\RetiredPosts;

class PostController extends Controller
{
    /** Category listing, e.g. /page/publication/news */
    public function index(string $section, string $category)
    {
        $meta = config("site.listings.{$category}");
        abort_unless($meta && $meta['section'] === $section, 404);

        $posts = Post::published()
            ->where('category', $category)
            // Undated articles are still real content; ordering falls back to
            // the record id so they do not vanish from the listing while they
            // wait for a human to confirm the date.
            ->orderByRaw('published_at IS NULL, published_at DESC, id DESC')
            ->paginate(12);

        $current = "/page/{$section}/{$category}";

        return view('posts.index', [
            'posts' => $posts,
            'meta' => $meta,
            'breadcrumb' => [
                ['label' => 'Beranda', 'url' => '/'],
                ['label' => $meta['group'], 'url' => config("site.siblings.{$section}.0.url")],
                ['label' => $meta['label'], 'url' => null],
            ],
            'tabs' => collect(config("site.siblings.{$section}", []))
                ->map(fn ($t) => $t + ['active' => $t['url'] === $current])
                ->all(),
        ]);
    }

    /** Detail page, matching the old site's /post/{category}/{slug} exactly. */
    public function show(string $category, string $slug)
    {
        // published() dipakai di sini juga, bukan hanya di daftar. Draf yang
        // tidak muncul di daftar tapi tetap terbuka bagi siapa pun yang menebak
        // URL-nya bukan draf; itu artikel terbit yang tidak ditautkan.
        // media ikut dimuat di muka: artikel bertipe Galeri merender seluruh
        // berkasnya, dan tanpa ini tiap foto menjadi satu kueri sendiri.
        $post = Post::published()->with('media')
            ->where('category', $category)->where('slug', $slug)->first();

        if (! $post) {
            // URL yang dulu ada di situs lama dijawab 410, bukan 404. Lihat
            // RetiredPosts soal kenapa hilang permanen, bukan dialihkan.
            // View dirender langsung, bukan lewat abort(410), supaya halamannya
            // bisa menawarkan daftar kategori tempat artikel itu dulu berada.
            if ($retired = RetiredPosts::categoryFor($slug)) {
                // Tautan ke daftar kategori hanya ditawarkan kalau daftarnya
                // memang berisi. she-activity kehilangan seluruh artikelnya,
                // dan mengirim pengunjung ke halaman kosong tidak menolong.
                $hasSiblings = Post::published()->where('category', $retired)->exists();

                return response()->view('errors.410', [
                    'category' => $hasSiblings ? $retired : null,
                ], 410);
            }

            abort(404);
        }

        $related = Post::published()
            ->where('category', $category)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('posts.show', compact('post', 'related'));
    }
}
