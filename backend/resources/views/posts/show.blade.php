@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->excerpt ?? $post->title)
@section('canonical', $post->url)
@section('og_type', 'article')
@if ($post->cover_image)
  @section('og_image', url(\App\Support\ImageVariants::best($post->cover_url, 1200)))
@endif

@push('head')
<script type="application/ld+json">
{!! \App\Support\StructuredData::newsArticle($post) !!}
</script>
@endpush

@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-logo-blue via-logo-blue to-logo-blue-dark pt-[72px] text-white">
  <div class="h-1.5 w-full" style="background:linear-gradient(90deg,#154F91,#FFE80A,#2A9941,#E03633,#154F91)"></div>
  <div class="absolute inset-0 opacity-10 hero-pattern"></div>
  <div class="relative mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <nav aria-label="Breadcrumb" class="mb-3 text-xs">
      <ol class="flex flex-wrap items-center gap-x-1.5">
        <li><a href="/" class="text-white/65 hover:text-white">Beranda</a></li>
        <li aria-hidden="true" class="text-white/35">/</li>
        <li><span class="font-semibold text-logo-yellow">{{ $post->category }}</span></li>
      </ol>
    </nav>
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
  <div class="prose-hasnur space-y-4 leading-relaxed text-slate-700">
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
