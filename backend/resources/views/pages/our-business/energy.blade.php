@extends('layouts.app')

@section('title', 'Energi')
@section('description', 'SBU Energi Hasnur Group, pertambangan batubara dan pembangkit tenaga mikro hidro.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Energi', 'url' => null]
        ]"
    title="Energi"
    description="SBU Energy menaungi sub holding di bidang pertambangan dengan kapasitas produksi yang terus tumbuh."
    :image="['src' => '/assets/img/pages/our-business-energy/banner.png', 'alt' => 'Energi, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => false],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => false],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => true],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => false],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => false],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => false]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">SBU Energy Hasnur Group telah mengembangkan Strategic Unit Business Energy yang menaungi sub holding di bidang usaha pertambangan dan pembangkit tenaga mikro hidro (PTMH). Dalam usaha pertambangan Hasnur Group memiliki visi untuk menjadi perusahaan pertambangan terkemuka di Indonesia dengan kapasitas produksi yang terus semakin meningkat dari tahun ke tahun untuk memenuhi kebutuhan konsumsi batubara nasional dan internasional.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Bisnis usaha pertambangan batubara Hasnur Group saat ini dikelola oleh dua anak perusahaan yaitu, PT Energi Batubara Lestari yang memiliki luas izin usaha pertambangan 1.896 ha dan PT Bhumi Rantau Energi memiliki luas izin usaha pertambangan 2.096 ha, berlokasi di Rantau, Kabupaten Tapin, Kalimantan Selatan. PT Energi Batubara Lestari dan PT Bhumi Rantau Energi sudah berpengalaman dalam tujuan pasar nasional dan internasional, seperti Thailand, China, India, Chile, Slovenia dsb.</p>
<x-img src="/assets/img/pages/our-business-energy/content-1.jpg" alt="Energi" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<x-img src="/assets/img/pages/our-business-energy/content-2.jpg" alt="Energi" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Kegiatan penambangan dilakukan dengan menerapkan praktek-praktek terbaik dalam pertambangan ( Good Mining Practices ) serta patuh terhadap ketentuan pemerintah yang berlaku termasuk dalam hubungannya dengan pengelolaan lingkungan hidup yang benar dan aspek kelangsungan usaha ( sustainable ). Selain terus meningkatkan produktivitas, Hasnur Group memiliki komitmen terhadap pelestarian lingkungan. Prosedur, sistem dan program pengawasan dilakukan secara berkala dan berkesinambungan untuk memastikan keseimbangan ekosistem terkendali.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PLTM Bayang Nyalo merupakan wujud implementasi Hasnur Group dalam memanfaatkan energi baru dan terbarukan (EBT) melalui entitas perusahaan PT Bayang Nyalo Hidro (BNH). Pembangkit Listrik energi terbarukan ini menggunakan tenaga air sebagai penggerak dengan memanfaatkan ketinggian dan debit air. Peralatan utama dalam memproduksi listrik yaitu turbin dan generator dibuat di Austria oleh salah satu manufaktur turbin air di dunia, Voith Hydro Austria. Relasi antara BNH dan Voith Hydro Austria dalam penyediaan mesin tersebut merupakan kerja sama antara BNH dan Voith Fuji Services (Jepang) dalam program Joint Crediting Mechanism (JCM) antara Jepang – Indonesia.</p>
<x-img src="/assets/img/pages/our-business-energy/content-3.jpg" alt="Energi" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PLTM Bayang Nyalo dengan kapasitas 6 MW akan menghasilkan energi listrik sebesar 52,56 GWh per tahun. Hadirnya PLTM Bayang Nyalo tidak hanya sekedar menyediakan sumber daya yang berkelanjutan tetapi juga mendukung diversifikasi energi. Langkah penting dalam menyelaraskan kebutuhan energi dan pelestarian lingkungan dimana PLTM Bayang Nyalo akan berkontribusi dalam mengurangi emisi CO2 sebesar 32,53 – 41,73 ribu ton per tahun. Nilai pengurangan emisi karbon tersebut setara dengan reduksi penggunaan 11,46 - 14,7 jt liter bahan bakar diesel atau 13,44 – 17,24 ribu ton batu bara per tahun.</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
