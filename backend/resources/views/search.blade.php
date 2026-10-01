@extends('layouts.app')

@section('title', $q !== '' ? __('search.title_with_query', ['q' => $q]) : __('search.title'))
@section('description', __('search.description'))
{{-- Halaman hasil pencarian tidak layak masuk indeks: isinya berubah menurut
     kata kunci, dan tiap kombinasi melahirkan satu URL lagi yang isinya nyaris
     sama. Itu persis bentuk yang dinilai Google sebagai halaman bernilai tipis. --}}
@section('noindex', true)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">

  <nav class="mb-6 text-xs font-semibold text-slate-400" aria-label="Breadcrumb">
    @foreach ($breadcrumb as $crumb)
      @if ($crumb['url'])
        <a href="{{ $crumb['url'] }}" class="hover:text-primary">{{ $crumb['label'] }}</a>
        <span class="mx-1">/</span>
      @else
        <span class="text-primary">{{ $crumb['label'] }}</span>
      @endif
    @endforeach
  </nav>

  <h1 class="text-3xl font-extrabold text-slate-800 sm:text-4xl">{{ __('search.title') }}</h1>

  {{-- Aksi formulir memakai URL berbahasa, bukan url('/cari') mentah: pembaca
       yang mencari dari /en akan terlempar ke versi Indonesia begitu menekan
       Cari, dan kehilangan bahasanya di tengah jalan. --}}
  <form role="search" data-search method="GET"
        action="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/cari')) }}"
        class="mt-6 flex flex-col gap-3 sm:flex-row">
    <label for="q" class="sr-only">{{ __('search.keyword_label') }}</label>
    <input type="search" name="q" id="q" value="{{ $q }}"
           placeholder="{{ __('search.keyword_placeholder') }}"
           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">

    <label for="tipe" class="sr-only">{{ __('search.type_label') }}</label>
    <select name="tipe" id="tipe"
            class="rounded-xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-primary focus:outline-none">
      <option value="">{{ __('search.all_types') }}</option>
      @foreach (\App\Enums\ContentType::cases() as $case)
        <option value="{{ $case->value }}" @selected($tipe === $case)>{{ $case->getLabel() }}</option>
      @endforeach
    </select>

    <label for="tag" class="sr-only">{{ __('search.tag_label') }}</label>
    <select name="tag" id="tag"
            class="rounded-xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-primary focus:outline-none">
      <option value="">{{ __('search.all_tags') }}</option>
      @foreach ($tags as $t)
        <option value="{{ $t->slug }}" @selected($tag === $t->slug)>{{ $t->name }}</option>
      @endforeach
    </select>

    <button type="submit"
            class="rounded-xl bg-primary px-6 py-3 font-semibold text-white transition hover:bg-primary/90">
      {{ __('search.submit') }}
    </button>
  </form>

  <p class="mt-6 text-sm text-slate-500" aria-live="polite">
    @if ($q === '' && ! $tipe && ! $tag)
      {{ __('search.prompt') }}
    @else
      {{ __('search.results_count', ['count' => $posts->total()]) }}
    @endif
  </p>

  <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($posts as $post)
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
          @if ($post->content_type)
            <span class="mb-2 w-fit rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500">
              {{ $post->content_type->getLabel() }}
            </span>
          @endif
          <h2 class="font-bold leading-snug text-slate-800 group-hover:text-primary">{{ $post->title }}</h2>
          <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $post->excerpt }}</p>
          <div class="mt-auto pt-4 text-xs font-semibold text-slate-400">
            {{ $post->published_at?->translatedFormat('d F Y') ?? __('post.date_unconfirmed') }}
          </div>
        </div>
      </a>
    @empty
      <p class="col-span-full rounded-2xl border border-dashed border-slate-200 p-10 text-center text-slate-500">
        @if ($q === '' && ! $tipe && ! $tag)
          {{ __('search.empty_initial') }}
        @else
          {{ __('search.empty_no_match') }}
        @endif
      </p>
    @endforelse
  </div>

  <div class="mt-10">{{ $posts->links() }}</div>
</div>
@endsection
