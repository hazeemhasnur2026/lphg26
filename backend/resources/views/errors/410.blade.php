@extends('layouts.app')

@section('title', 'Artikel sudah tidak tersedia')
@section('description', 'Artikel ini sudah tidak diterbitkan lagi di situs Hasnur Group.')

@push('head')
<meta name="robots" content="noindex">
@endpush

@section('content')
{{-- Pengunjung halaman ini datang dari hasil pencarian yang menunjuk artikel
     yang sudah dihapus. Yang berguna baginya adalah daftar kategori tempat
     artikel itu dulu berada, bukan halaman error kerangka kerja. --}}
<section class="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6 lg:px-8">
  <p class="text-sm font-bold uppercase tracking-widest text-accent">410 · Sudah tidak tersedia</p>
  <h1 class="mt-4 text-3xl font-extrabold text-primary sm:text-4xl">Artikel ini sudah tidak diterbitkan</h1>
  <p class="mt-5 leading-relaxed text-slate-600">
    Halaman yang Anda tuju pernah ada di situs lama Hasnur Group, tetapi artikelnya sudah tidak lagi diterbitkan.
  </p>

  <div class="mt-10 flex flex-wrap justify-center gap-3">
    @if (! empty($category) && config("site.listings.{$category}"))
      @php ($listing = config("site.listings.{$category}"))
      <a href="/page/{{ $listing['section'] }}/{{ $category }}"
         class="rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
        Lihat {{ $listing['label'] }}
      </a>
    @endif
    <a href="/" class="rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-600 transition hover:text-primary">
      Kembali ke beranda
    </a>
  </div>
</section>
@endsection
