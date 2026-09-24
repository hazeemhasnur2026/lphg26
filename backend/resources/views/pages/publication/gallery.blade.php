@extends('layouts.app')

@section('title', 'Galeri')
@section('description', 'Galeri foto kegiatan Hasnur Group.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Publikasi', 'url' => '/page/publication/latest-update'],
            ['label' => 'Galeri', 'url' => null]
        ]"
    title="Galeri"
    description="Galeri foto kegiatan Hasnur Group."
    :image="['src' => '/assets/img/pages/publication-gallery/banner.jpg', 'alt' => 'Galeri, Hasnur Group']"
    :tabs="[
            ['label' => 'Update Terbaru', 'url' => '/page/publication/latest-update', 'active' => false],
            ['label' => 'Berita', 'url' => '/page/publication/news', 'active' => false],
            ['label' => 'Press Release', 'url' => '/page/publication/press-release', 'active' => false],
            ['label' => 'Multimedia', 'url' => '/page/publication/multimedia', 'active' => false],
            ['label' => 'Galeri', 'url' => '/page/publication/gallery', 'active' => true]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="/page/publication/latest-update" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Update Terbaru</a><a href="/page/publication/news" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Berita</a><a href="/page/publication/press-release" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Press Release</a><a href="/page/publication/multimedia" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Multimedia</a><a href="/page/publication/gallery" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">Galeri</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-sm text-slate-500 mb-10">Galeri foto kegiatan Hasnur Group, diambil dari hasnurgroup.com.</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <a href="/assets/img/gallery/01-hasnur-group-she-activity.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/01-hasnur-group-she-activity.jpg" alt="Hasnur Group SHE Activity" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">Hasnur Group SHE Activity</p>
        </div>
      </a>
      <a href="/assets/img/gallery/02-hasnur-group-csr-activity.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/02-hasnur-group-csr-activity.jpg" alt="Hasnur Group CSR Activity" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">Hasnur Group CSR Activity</p>
        </div>
      </a>
      <a href="/assets/img/gallery/03-pt-hasnur-internasional-shipping-beri-ba.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/03-pt-hasnur-internasional-shipping-beri-ba.jpg" alt="PT Hasnur Internasional Shipping Beri Bantuan Banjir" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">PT Hasnur Internasional Shipping Beri Bantuan Banjir</p>
        </div>
      </a>
      <a href="/assets/img/gallery/04-hasnur-group-memberikan-bantuan-banjir-k.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/04-hasnur-group-memberikan-bantuan-banjir-k.jpg" alt="Hasnur Group Memberikan Bantuan Banjir Kalsel" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">Hasnur Group Memberikan Bantuan Banjir Kalsel</p>
        </div>
      </a>
      <a href="/assets/img/gallery/05-yayasan-hasnur-centre-menyerahkan-bantua.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/05-yayasan-hasnur-centre-menyerahkan-bantua.jpg" alt="Yayasan Hasnur Centre Menyerahkan Bantuan Banjir" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">Yayasan Hasnur Centre Menyerahkan Bantuan Banjir</p>
        </div>
      </a>
      <a href="/assets/img/gallery/06-pt-hasnur-resources-terminal-peduli-benc.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/06-pt-hasnur-resources-terminal-peduli-benc.jpg" alt="PT Hasnur Resources Terminal Peduli Bencana Gempa Bumi" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">PT Hasnur Resources Terminal Peduli Bencana Gempa Bumi</p>
        </div>
      </a>
      <a href="/assets/img/gallery/07-hasnur-group-memberikan-bantuan-penangan.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/07-hasnur-group-memberikan-bantuan-penangan.jpg" alt="Hasnur Group Memberikan Bantuan Penanganan Covid-19" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">Hasnur Group Memberikan Bantuan Penanganan Covid-19</p>
        </div>
      </a>
      <a href="/assets/img/gallery/08-hasnur-group-memberikan-bantuan-penangan.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/08-hasnur-group-memberikan-bantuan-penangan.jpg" alt="Hasnur Group Memberikan Bantuan Penanganan Covid-19" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">Hasnur Group Memberikan Bantuan Penanganan Covid-19</p>
        </div>
      </a>
      <a href="/assets/img/gallery/09-she-hasnur-group-tanggap-darurat-di-sbu.jpg" target="_blank" rel="noopener" class="reveal group relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition block">
        <x-img src="/assets/img/gallery/09-she-hasnur-group-tanggap-darurat-di-sbu.jpg" alt="SHE Hasnur Group Tanggap Darurat di SBU Forestry" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 flex items-end p-4">
          <p class="text-white text-sm font-semibold leading-snug">SHE Hasnur Group Tanggap Darurat di SBU Forestry</p>
        </div>
      </a>
    </div>
  </div>
</section>
@endsection
