@extends('layouts.app')

@section('title', 'Kebutuhan Pelanggan')
@section('description', 'SBU Kebutuhan Pelanggan Hasnur Group, bisnis media, gaya hidup, dan kesehatan.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Kebutuhan Pelanggan', 'url' => null]
        ]"
    title="Kebutuhan Pelanggan"
    description="Membawahi usaha bisnis di bidang media, gaya hidup, dan kesehatan."
    :image="['src' => '/assets/img/pages/our-business-consumer/banner.png', 'alt' => 'Kebutuhan Pelanggan, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => false],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => false],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => false],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => false],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => true],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => false]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group mengembangkan strategic business unit Consumer yang membawahi usaha bisnis di bidang Media, gaya hidup dan kesehatan,.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Bisnis Media Hasnur Group dikelola oleh PT Hasnur Media Citra sebagai holding yang membawahi beberapa entitas. Pada awalnya bisnis media bergerak di penerbitan koran dan juga tabloid serta menjalankan usaha media di bidang penyiaran radio berita dan olahraga di Kalimantan Selatan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Saat ini fokus bisnis usaha dijalankan oleh PT Putera Barito Berbakti yang menaungi klub sepakbola PS Barito Putera dan Sekolah Olahraga SOBP PS Barito Putera (Singkatan dari: Persatuan Sepakbola Barito Putera) adalah klub sepakbola Indonesia yang berbasis di Kota Banjarmasin, Kalimantan Selatan. PS Barito Putera didirikan pada tanggal 21 April 1988 dan berkompetisi di liga sepakbola tertinggi di Indonesia, yaitu Liga 1 sejak musim 2013.</p>
<x-img src="/assets/img/pages/our-business-consumer/content-1.jpg" alt="Kebutuhan Pelanggan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">SOBP merupakan institusi yang mengedepankan hak-hak anak dalam masa tumbuh kembangnya sehingga menjadi generasi muda yang bugar, berkarakter, bertaqwa dan berprestasi.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dalam perkembangannya, SOBP akan selalu dinamis dalam mengembangkan diri demi peningkatan kualitas layanan dan pendidikan sehingga menghasilkan generasi muda yang sesuai dengan harapan bersama. Oleh karena itu kami akan selalu melakukan pengembangan, baik dari segi pedagogi hingga kurikulum, sehingga SOBP mampu terus ada, dan mampu menjadi bagian penting bagi pengembangan olahraga di Indonesia.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Kehadiran PT Hasnur Media Citra selain sebagai pengembangan unit usaha bisnis dalam korporasi Hasnur Group, juga untuk menjaga integritas, citra dan brand perusahaan menghadapi era globalisasi dan komunikasi digital yang semakin meluas dan terbuka di dalam negeri maupun di luar negeri.  PT Hasnur Media Citra memiliki skala jasa dan bisnis dalam kegiatan usahanya, yaitu :</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Konvergensi dan Sinergi Media</li><li class="text-slate-600 leading-relaxed">Agen Iklan, Promosi, dan Percetakan</li><li class="text-slate-600 leading-relaxed">Event Organizer &amp; Rental Equipment Produksi</li><li class="text-slate-600 leading-relaxed">Riset, Survei dan Litbang</li><li class="text-slate-600 leading-relaxed">Store, Merchandise dan PS Barito Putera Heritage</li></ul>
<x-img src="/assets/img/pages/our-business-consumer/content-2.jpg" alt="Kebutuhan Pelanggan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Tahun 2023, Hasnur Group memulai kegiatan bisnis baru di bidang jasa Kesehatan, melalui investasi pada PT Intibios Persada Sejahtera. Intibios merupakan pusat kesehatan masyarakat yang holistik yang menyediakan servis Kesehatan dari test, konsultasi, diagnostik, dan sampai penjualan obat melalui laboratorium, klinik, serta apoteknya untuk masyarakat Indonesia. Sampai saat ini, Intibios memiliki 10 outlet yang tersebar di 7 kota besar di Indonesia, seperti Jabodetabek, Bali, Bandung, Semarang, Yogyakarta, Surabaya, dan Cirebon.</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
