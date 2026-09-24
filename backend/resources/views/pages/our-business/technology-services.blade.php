@extends('layouts.app')

@section('title', 'Teknologi & Jasa')
@section('description', 'SBU Teknologi & Jasa Hasnur Group, pengembangan teknologi informasi dan layanan jasa.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Teknologi & Jasa', 'url' => null]
        ]"
    title="Teknologi &amp; Jasa"
    description="Menaungi unit bisnis di bidang pengembangan teknologi dan jasa layanan."
    :image="['src' => '/assets/img/pages/our-business-technology-services/banner.png', 'alt' => 'Teknologi & Jasa, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => false],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => false],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => false],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => true],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => false],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => false],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => false]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">SBU Technology dan Services menaungi unit bisnis di bidang pengembangan teknologi dan jasa layanan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Informasi Teknologi merupakan Perusahaan IT Konsultan yang didirikan berdasarkan ide dan kreativitas dalam memberikan solusi, pengembangan serta implementasi teknologi informasi dan komunikasi. PT Hasnur Informasi Teknologi memberikan layanan yang berkualitas dan bernilai tinggi dalam memberikan solusi atas masalah yang dihadapi oleh pengguna jasa dengan menyediakan  pengembangan software, instalasi dan konfigurasi hardware, konsultasi dan training center di bidang teknologi informasi dan komunikasi untuk memaksimalkan produktivitas dan profitabilitas pengguna jasa, dengan lingkup pekerjaan sebagai berikut :</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Jaringan dan Infrastruktur (Internet, WAN, LAN, Point to Point,Pengadaan Server dan lain-lain)</li><li class="text-slate-600 leading-relaxed">Pengembangan Hardware</li><li class="text-slate-600 leading-relaxed">Sistem Program Jembatan Timbang</li><li class="text-slate-600 leading-relaxed">Pengembangan dan Implementasi Enterprise Resources Planning.</li><li class="text-slate-600 leading-relaxed">Pengembangan dan Implementasi Business Intelligence</li><li class="text-slate-600 leading-relaxed">Pengembangan dan Implementasi aplikasi berbasis website dan mobile aplikasi.</li><li class="text-slate-600 leading-relaxed">Manage Services ICT</li><li class="text-slate-600 leading-relaxed">Jasa Konsultasi IT</li></ul>
<p class="reveal text-slate-600 leading-relaxed mb-4">Melalui pengelolaan yang profesional PT Hasnur Informasi Teknologi memiliki komitmen untuk dapat menjadi mitra strategis terpercaya dalam bidang teknologi informasi dan komunikasi dengan reputasi internasional.</p>
<x-img src="/assets/img/pages/our-business-technology-services/content-1.jpg" alt="Teknologi & Jasa" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<x-img src="/assets/img/pages/our-business-technology-services/content-2.jpg" alt="Teknologi & Jasa" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Magma Sigma Utama (MSU) sebagai anak perusahaan Hasnur Group yang menjalankan usaha end to end logistics tambang, agroforestry, energy, minyak dan gas : transportasi dan trucking service , niaga part, proyek management 4 th Party Logistic dan jasa logistik pendukung lainnya.  Saat ini PT MSU memiliki dua anak Perusahaan yaitu PT Sinergi Sigra Sejahtera (PT SSS) dan PT Mitra Sigra Utama (PT MSU) dengan proyek usaha tersebar di wilayah Kalimantan, Sulawesi dan Sumatera.  Proyek tersebut berupa kegiatan transportasi dan trucking service batubara di Kalimantan Selatan dengan kapasitas minimal 2 juta ton pertahun, transportasi solar 35 juta liter per tahun.  Selain itu pembangunan infrastruktur logistic penambangan batu gamping di Morowali Utara serta mobilisasi oil rig kapasitas 1500-2000 HP dari Sidoarjo ke Ponorogo Jawa Timur dan Prabumulih Sumatera Selatan ke Tonga Sumatera Utara.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT MSU bertekad memberikan layanan terbaik dengan kualitas prima untuk memenangkan pasar di luar grup dan menunjang kemajuan usaha di dalam grup dengan moto “Mitra yang Amanah”. Guna mewujudkan hal tersebut PT MSU secara berkelanjutan dan konsisten senantiasa melakukan :</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Pengembangan kompetensi SDM agar semakin amanah, cerdas teknologi, profesional, berintegritas dan ramah.</li><li class="text-slate-600 leading-relaxed">Operation Excellent dengan prinsip Tepat mutu, Tepat waktu dan Tepat Tempat</li><li class="text-slate-600 leading-relaxed">Perbaikan secara berkelanjutan dengan mengoptimalkan pengelolaan Sigma Tata Kelola Terintegrasi (SIGMAT) dan standar manajemen mutu internasional seperti ISO 9001: 2015 dan 45001: 2018. Akhir tahun 2023, PT MSU memperoleh ISO 55001  yang mengatur praktik terbaik dalam manajemen aset.</li></ul>
<x-img src="/assets/img/pages/our-business-technology-services/content-3.jpg" alt="Teknologi & Jasa" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group juga juga memiliki anak perusahaan yang menjalankan usaha jasa kontraktor pertambangan, yaitu (https://hasnurriungsinergi.co.id/). PT Hasnur Riung Sinergi terus berupaya mendukung pertumbuhan ekonomi dengan memberikan kontribusi positif bagi bangsa sejak tahun 2011 dengan area operasional IUP PT Energi Batubara Lestari, IUP PT Bhumi Rantau Energi dan (PKP2B) PT Antang Gunung Meratus yang berlokasi di Kabupaten Tapin, Kalimantan Selatan. Pencapaian kinerja kami pada tahun terakhir sebanyak 66,5 Juta OB (BCM) dan 11,8 juta Coal (MT) yang didukung dengan keterlibatan tenaga kerja sebanyak 3500 karyawan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dalam kegiatannya, perusahaan meyakini keselamatan akan meningkatkan kualitas hidup dan keunggulan perusahaan sehingga perusahaan berkomitmen untuk menerapkan dan mengembangkan Hasnur Riung Sinergi Integrated Management System (HIMS) yang berfungsi sebagai sebuah sistem dengan mengacu kepada persyaratan Internasional (ISO 9001, ISO 14001 dan ISO 45001) maupun persyaratan nasional (SMK3 dan SMKP), serta kondisi isu internal dan eksternal yang berhubungan dengan operasional perusahaan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Riung Sinergi juga memiliki anak usaha yaitu PT Fajar Anugerah Dinamika, yang merupakan unit bisnis dibidang kontraktor jasa pertambangan  serta PT Sigma Rekayasa Prima, unit bisnis dengan layanan berupa desain fabrikasi untuk industri pertambangan, alat berat serta maritim. Kami senantiasa  menciptakan aktivitas operasional yang aman, andal dan ramah lingkungan untuk mewujudkan Operational Excellence.</p>
<x-img src="/assets/img/pages/our-business-technology-services/content-4.jpg" alt="Teknologi & Jasa" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Cipta Karya adalah sebuah perseroan terbatas di bawah naungan Hasnur Group yang berkomitmen menjadi perusahaan di bidang Engineering - Procurement - Construction (EPC), Manajemen Konstruksi, penyedia material konstruksi yang mandiri dan terdepan di kawasan Kalimantan pada khususnya dan Indonesia pada umumnya, dengan mengutamakan terjaganya mutu, kepastian waktu penyelesaian pekerjaan dan kewajaran anggaran, sehingga dapat memberikan manfaat bagi para pemangku kepentingan.</p>
<x-img src="/assets/img/pages/our-business-technology-services/content-5.jpg" alt="Teknologi & Jasa" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT HCK dalam menjalankan kegiatan usahanya, senantiasa meningkatkan kompetensi sumber daya manusia untuk pertumbuhan yang berkelanjutan, menggunakan teknologi terkini yang relevan dengan kegiatan operasional, memberikan layanan dan produk berkualitas prima secara tepat waktu dan harga yang wajar untuk mewujudkan kepuasan konsumen yang berkesinambungan sehingga tercipta loyalitas konsumen, mengutamakan aspek keselamatan dan kesehatan kerja serta dampak pada lingkungan, menerapkan metode dan standar operasional prima dengan fokus pada biaya, mutu, dan waktu. Seluruh kegiatan operasional PT Hasnur Cipta Karya dilaksanakan secara profesional yang berlandaskan Tujuh Nilai inti Hasnur Group.</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
