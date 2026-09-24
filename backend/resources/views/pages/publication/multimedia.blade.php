@extends('layouts.app')

@section('title', 'Multimedia')
@section('description', 'Video dan dokumentasi multimedia Hasnur Group.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Publikasi', 'url' => '/page/publication/latest-update'],
            ['label' => 'Multimedia', 'url' => null]
        ]"
    title="Multimedia"
    description="Kumpulan materi video dan multimedia Hasnur Group."
    :image="['src' => '/assets/img/pages/publication-multimedia/banner.jpg', 'alt' => 'Multimedia, Hasnur Group']"
    :tabs="[
            ['label' => 'Update Terbaru', 'url' => '/page/publication/latest-update', 'active' => false],
            ['label' => 'Berita', 'url' => '/page/publication/news', 'active' => false],
            ['label' => 'Press Release', 'url' => '/page/publication/press-release', 'active' => false],
            ['label' => 'Multimedia', 'url' => '/page/publication/multimedia', 'active' => true],
            ['label' => 'Galeri', 'url' => '/page/publication/gallery', 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="/page/publication/latest-update" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Update Terbaru</a><a href="/page/publication/news" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Berita</a><a href="/page/publication/press-release" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Press Release</a><a href="/page/publication/multimedia" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">Multimedia</a><a href="/page/publication/gallery" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Galeri</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-sm text-slate-500 mb-10">Video profil dan dokumentasi Hasnur Group.</p>
    <div class="grid gap-8">
      <a href="https://www.youtube.com/watch?v=rpsLPpff8Wg" target="_blank" rel="noopener" class="reveal group relative rounded-2xl overflow-hidden shadow-lg block bg-black">
        <x-img src="/assets/img/pages/publication-multimedia/content-1.jpg" alt="Video Profile Hasnur Group" class="w-full h-auto object-cover opacity-90 group-hover:opacity-70 transition" loading="lazy" sizes="(min-width: 1280px) 1280px, 100vw" />
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center group-hover:scale-110 transition">
            <svg class="w-6 h-6 text-primary ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
          </div>
        </div>
        <p class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 to-transparent text-white text-sm font-semibold p-4">Video Profile Hasnur Group</p>
      </a>
    </div>
  </div>
</section>
@endsection
