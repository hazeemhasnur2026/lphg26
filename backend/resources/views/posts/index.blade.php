@extends('layouts.app')

@section('title', $meta['label'])
@section('description', 'Kumpulan '.$meta['label'].' dari Hasnur Group.')

@section('content')
<x-page-hero
    :breadcrumb="$breadcrumb"
    :title="$meta['label']"
    :description="$posts->total().' publikasi dari Hasnur Group.'"
    :image="['src' => asset('assets/img/pages/'.$meta['banner'].'/banner.jpg'), 'alt' => $meta['label']]"
    :tabs="$tabs" />

<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($posts as $post)
      {{-- Coverage published elsewhere opens at the outlet: we hold the
           citation, not the article, so there is no page of ours to open. --}}
      <a href="{{ $post->url }}"
         @if ($post->isExternal()) target="_blank" rel="noopener" @endif
         class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white transition hover:shadow-xl">
        @if ($post->cover_image)
          <div class="aspect-video overflow-hidden bg-slate-100">
            <x-img :src="$post->cover_url" alt="" loading="lazy"
                   sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw"
                   class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
          </div>
        @endif
        <div class="flex flex-1 flex-col p-6">
          <h2 class="font-bold leading-snug text-slate-800 group-hover:text-primary">{{ $post->title }}</h2>
          <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $post->excerpt }}</p>
          <div class="mt-auto pt-4 text-xs font-semibold text-slate-400">
            {{ $post->published_at?->translatedFormat('d F Y') ?? 'tanggal belum dikonfirmasi' }}
            @if ($post->isExternal())
              <span class="mt-1 flex items-center gap-1 text-slate-500">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5M15 3h6m0 0v6m0-6L10.5 13.5"/>
                </svg>
                {{ $post->source_name }}
              </span>
            @endif
          </div>
        </div>
      </a>
    @empty
      {{-- she-activity kehilangan seluruh artikelnya saat 26 artikel tanpa
           tanggal dihapus. Halamannya tetap bisa dibuka karena kategorinya
           masih ada di navigasi, tapi tidak diajukan ke sitemap sampai ada
           isinya. --}}
      <p class="col-span-full rounded-2xl bg-slate-50 p-10 text-center text-slate-500">
        Belum ada publikasi pada kategori ini.
      </p>
    @endforelse
  </div>

  <div class="mt-10">{{ $posts->links() }}</div>
</div>
@endsection
