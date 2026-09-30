<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

/**
 * Pencarian situs (CMS-08).
 *
 * LIKE, bukan mesin pencari tersendiri. Isi situs ini berjumlah puluhan artikel,
 * bukan puluhan ribu, dan SQLite menjawabnya dalam milidetik. Memasang
 * Meilisearch atau Elasticsearch berarti satu layanan lagi yang harus menyala,
 * dicadangkan, dan diperbarui, demi kecepatan yang tidak akan terasa.
 *
 * Yang perlu diawasi adalah kapan alasan ini berhenti berlaku: bila artikel
 * menembus ribuan dan pencarian mulai terasa lambat, jawabannya bukan mengakali
 * LIKE melainkan mengganti lapisan ini.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $tipe = ContentType::tryFrom((string) $request->query('tipe'));
        $tag = $request->query('tag');
        $kategori = $request->query('kategori');

        $posts = Post::published()
            ->with('tags')
            ->when($q !== '', function ($query) use ($q) {
                // Tanda persen dan garis bawah punya arti khusus di LIKE. Tanpa
                // dilepas, pencarian "100%" akan cocok dengan segalanya.
                $pola = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

                $query->where(fn ($w) => $w
                    ->where('title', 'like', $pola)
                    ->orWhere('excerpt', 'like', $pola)
                    ->orWhere('body', 'like', $pola));
            })
            ->when($tipe, fn ($query) => $query->where('content_type', $tipe))
            ->when($kategori && config("site.listings.{$kategori}"),
                fn ($query) => $query->where('category', $kategori))
            ->when($tag, fn ($query) => $query->whereHas('tags', fn ($t) => $t->where('slug', $tag)))
            ->orderByRaw('published_at IS NULL, published_at DESC, id DESC')
            ->paginate(12)
            ->withQueryString();

        return view('search', [
            'posts' => $posts,
            'q' => $q,
            'tipe' => $tipe,
            'tag' => $tag,
            'kategori' => $kategori,
            'tags' => Tag::has('posts')->orderBy('name')->get(),
            'breadcrumb' => [
                ['label' => 'Beranda', 'url' => '/'],
                ['label' => 'Pencarian', 'url' => null],
            ],
        ]);
    }
}
