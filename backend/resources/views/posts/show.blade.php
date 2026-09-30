@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->meta_description_text)
@section('canonical', $post->canonical_url ?: $post->url)
@section('og_type', 'article')
@if ($post->og_image_url)
  @section('og_image', url(\App\Support\ImageVariants::best($post->og_image_url, 1200)))
@endif
@if ($post->noindex)
  @section('noindex', true)
@endif

@push('head')
<script type="application/ld+json">
{!! \App\Support\StructuredData::newsArticle($post) !!}
</script>
@endpush

@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-logo-blue via-logo-blue to-logo-blue-dark pt-[72px] text-white">
  <x-brand-strip />
  <div class="absolute inset-0 opacity-10 hero-pattern"></div>
  <div class="relative mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <nav aria-label="Breadcrumb" class="mb-3 text-xs">
      <ol class="flex flex-wrap items-center gap-x-1.5">
        <li><a href="/" class="text-white/65 hover:text-white">Beranda</a></li>
        <li aria-hidden="true" class="text-white/35">/</li>
        <li><span class="font-semibold text-logo-yellow">{{ $post->category }}</span></li>
      </ol>
    </nav>
    @if ($post->content_type)
      <span class="mb-3 inline-block rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide">
        {{ $post->content_type->getLabel() }}
      </span>
    @endif
    <h1 class="text-3xl font-extrabold leading-tight md:text-4xl">{{ $post->title }}</h1>
    <p class="mt-4 text-sm text-white/70">
      @if ($post->published_at)
        {{ $post->published_at->translatedFormat('d F Y') }}
      @else
        <span class="rounded-full bg-white/15 px-2 py-0.5">tanggal belum dikonfirmasi</span>
      @endif
    </p>
  </div>
</section>

<article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

  {{--
    Isi khas tipe konten ditaruh di atas badan artikel, bukan di bawahnya.

    Untuk Laporan dan Publikasi, badan artikelnya adalah pengantar; yang dicari
    pembaca adalah berkasnya. Menaruh tombol unduh di dasar halaman berarti
    setiap orang harus menggulir melewati pengantar yang tidak ia minta.
    Hal yang sama berlaku untuk video dan galeri: itulah kontennya, bukan
    lampirannya.
  --}}
  @if ($post->content_type?->isDocument() && $post->document_url)
    @include('posts.partials.dokumen')
  @endif

  @if ($post->content_type === \App\Enums\ContentType::Video && $post->video_url)
    @include('posts.partials.video')
  @endif

  @if ($post->content_type === \App\Enums\ContentType::Gallery && $post->media->isNotEmpty())
    @include('posts.partials.galeri')
  @endif

  <div class="prose-hasnur mt-8 space-y-4 leading-relaxed text-slate-700">
    {!! $post->body_html !!}
  </div>

  @if ($post->source_url)
    <p class="mt-10 border-t border-slate-100 pt-6 text-xs text-slate-400">
      Diimpor dari <a href="{{ $post->source_url }}" class="underline" rel="noopener" target="_blank">situs lama</a>.
    </p>
  @endif
</article>

@if ($related->isNotEmpty())
<section class="bg-slate-50 py-14">
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-extrabold text-primary">Artikel lain</h2>
    <div class="mt-8 grid gap-6 sm:grid-cols-3">
      @foreach ($related as $item)
        <a href="{{ $item->url }}" class="group rounded-2xl border border-slate-100 bg-white p-5 transition hover:shadow-lg">
          <h3 class="font-bold leading-snug text-slate-800 group-hover:text-primary">{{ $item->title }}</h3>
          <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $item->excerpt }}</p>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
