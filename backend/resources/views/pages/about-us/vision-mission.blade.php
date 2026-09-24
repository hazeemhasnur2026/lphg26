@extends('layouts.app')

@section('title', 'Visi Misi')
@section('description', 'Visi dan misi Hasnur Group dalam membangun perusahaan mandiri dengan reputasi internasional.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Tentang Kami', 'url' => '/page/about-us/overview'],
            ['label' => 'Visi Misi', 'url' => null]
        ]"
    title="Visi Misi"
    description="Membangun perusahaan-perusahaan mandiri dengan reputasi internasional yang membawa kebanggaan bagi bangsa."
    :image="['src' => '/assets/img/pages/about-us-vision-mission/banner.jpg', 'alt' => 'Visi Misi, Hasnur Group']"
    :tabs="[
            ['label' => 'Ikhtisar', 'url' => '/page/about-us/overview', 'active' => false],
            ['label' => 'Visi Misi', 'url' => '/page/about-us/vision-mission', 'active' => true],
            ['label' => 'Nilai Inti', 'url' => '/page/about-us/core-value', 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="/page/about-us/overview" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Ikhtisar</a><a href="/page/about-us/vision-mission" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">Visi Misi</a><a href="/page/about-us/core-value" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Nilai Inti</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">VISI</h2>
<p class="reveal text-slate-600 leading-relaxed mb-4">Membangun perusahaan-perusahaan mandiri dengan reputasi internasional yang membawa kebanggaan bagi seluruh pemangku kepentingan serta memberikan kontribusi perbaikan masyarakat, bangsa dan dunia.</p>
<h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">MISI</h2>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Menyediakan kesempatan yang sama untuk setiap orang mengembangkan potensinya dalam semangat kebersamaan</li><li class="text-slate-600 leading-relaxed">Mengoptimalkan nilai tambah usaha yang terbaik untuk pelanggan dengan tetap memelihara daya dukung lingkungan</li><li class="text-slate-600 leading-relaxed">Menerapkan tata kelola korporasi yang baik di seluruh bidang usaha</li><li class="text-slate-600 leading-relaxed">Menjalankan tanggung jawab sosial korporasi secara konsisten</li></ul>
  </div>
</section>
@endsection
