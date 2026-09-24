@extends('layouts.app')

@section('title', 'Ikhtisar')
@section('description', 'Sekilas tentang Hasnur Group, grup usaha yang didirikan oleh H. Abdussamad Sulaiman HB dan Hj. Nurhayati sejak 1966.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Tentang Kami', 'url' => '/page/about-us/overview'],
            ['label' => 'Ikhtisar', 'url' => null]
        ]"
    title="Ikhtisar"
    description="Grup usaha yang didirikan pengusaha asli Kalimantan, H. Abdussamad Sulaiman HB bersama Hj. Nurhayati, dan tumbuh dari usaha angkutan sungai sejak 1966."
    :image="['src' => '/assets/img/pages/about-us-overview/banner.jpg', 'alt' => 'Ikhtisar, Hasnur Group']"
    :tabs="[
            ['label' => 'Ikhtisar', 'url' => '/page/about-us/overview', 'active' => true],
            ['label' => 'Visi Misi', 'url' => '/page/about-us/vision-mission', 'active' => false],
            ['label' => 'Nilai Inti', 'url' => '/page/about-us/core-value', 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="/page/about-us/overview" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">Ikhtisar</a><a href="/page/about-us/vision-mission" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Visi Misi</a><a href="/page/about-us/core-value" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Nilai Inti</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">SEKILAS HASNUR</h2>
<p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group merupakan suatu grup usaha yang didirikan oleh pengusaha asli Kalimantan, yaitu Almarhum H. Abdussamad Sulaiman HB beserta istri beliau Almarhumah Hj. Nurhayati yang merintis usaha di bidang angkutan sungai sebagai cikal bakal berdirinya Hasnur Group pada tahun 1966. HASNUR merupakan gabungan nama dari H. Abdussamad Sulaiman HB (HAS) dan Hj. Nurhayati (NUR).</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Kegiatan usaha Hasnur Group yang dimulai dari angkutan sungai, galangan kapal, dan sektor kehutanan terus tumbuh dan berkembang ke berbagai sektor usaha antara lain pertambangan batubara, jalan dan terminal khusus batubara, agribisnis, transportasi, pelayaran, konstruksi, klub sepakbola profesional, media massa, dan pendidikan sekolah olahraga. Selain menjalankan bisnis usaha, Hasnur Group juga aktif dalam kegiatan sosial melalui Yayasan Hasnur Centre yang menaungi beberapa institusi pendidikan mulai dari pendidikan usia dini sampai dengan tingkat perguruan tinggi di Kalimantan Selatan.</p>
<x-img src="/assets/img/pages/about-us-overview/content-1.jpg" alt="Ikhtisar" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Pada perjalanannya Hasnur Group melakukan restrukturisasi bisnis dan usahanya serta menerapkan prinsip-prinsip pengelolaan usaha yang modern dengan tetap mengedepankan nilai -nilai religi dan kearifan lokal. Dengan pengalaman lebih dari 50 tahun Hasnur Group saat ini memiliki 6 Strategic Business Unit (SBU) yang saling terintegrasi yaitu Forestry, Mining, Agribusiness, Services, Media, and Logistic .</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Para manajemen yang diangkat merupakan profesional yang memiliki komitmen tinggi bahwa perusahaan harus berkembang bukan hanya untuk kepentingan para pemegang saham dan karyawan, namun juga untuk kemajuan masyarakat, pemerintah daerah dan pusat, serta pihak-pihak lain selaku pemangku kepentingan, baik di masa sekarang maupun masa mendatang. Hasnur Group terus melakukan kegiatan usahanya dengan satu tujuan "Grow Together Develop the Future" . Tumbuh dan Berkembang Bersama untuk Membangun Masa Depan.</p>
  </div>
</section>
@endsection
