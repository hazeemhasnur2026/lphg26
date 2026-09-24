@extends('layouts.app')

@section('title', 'Logistik')
@section('description', 'SBU Logistik Hasnur Group, jasa transportasi dan logistik melalui sungai dan laut.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Logistik', 'url' => null]
        ]"
    title="Logistik"
    description="SBU Logistik dikembangkan untuk mendukung kebutuhan logistik internal grup maupun mitra eksternal."
    :image="['src' => '/assets/img/pages/our-business-logistic/banner.png', 'alt' => 'Logistik, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => true],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => false],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => false],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => false],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => false],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => false]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group mengembangkan Strategic Business Unit (SBU) Logistik untuk mendukung bisnis-bisnis di lingkungan Hasnur Group dan juga mitra bisnis lainnya.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Internasional Shipping Tbk (HIS) bergerak di sektor usaha logistik dan transportasi komoditas melalui sungai dan laut untuk pasar domestik maupun kawasan Asia. Saat ini HIS memiliki 14 set kapal tunda ( tugboat ) dan tongkang ( barge ) dengan kapasitas angkut yang bervariasi, mulai dari 7.500 metrik ton (MT) hingga 10.000 MT. Dengan kapasitas angkut per tahun mencapai 10 juta MT, HIS memberikan jasa pengangkutan berbagai komoditas dari pelabuhan loading sampai pelabuhan bongkar, baik domestik maupun internasional dengan keunggulan operation excellence , aman, tepat waktu, dan efisien.</p>
<x-img src="/assets/img/pages/our-business-logistic/content-1.jpg" alt="Logistik" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Sebagai bagian dari SBU Logistik Hasnur Group Indonesia, HIS memiliki anak perusahaan yakni PT Hasnur Resources Terminal (HRT) dan PT Hasnur Mitra Sarana (HMS). HRT bergerak pada bidang usaha pengelolaan terminal dan fasilitas kepelabuhan. HRT memiliki izin Badan Usaha Pelabuhan yang mencakup sembilan bidang usaha, yaitu:</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan jasa dermaga untuk bertambat;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan pengisian bahan bakar dan pelayanan air bersih;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan fasilitas naik turun penumpang dan/ atau kendaraan;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan jasa dermaga untuk pelaksanaan kegiatan bongkar muat barang dan peti kemas;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan jasa gudang dan tempat penimbunan barang, alat bongkar muat, serta peralatan pelabuhan;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan jasa terminal peti kemas, curah cair, curah kering, dan Ro-Ro;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan jasa bongkar muat barang;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan pusat distribusi dan konsolidasi barang;</li><li class="text-slate-600 leading-relaxed">Penyediaan dan/ atau pelayanan jasa penundaan kapal.</li></ul>
<x-img src="/assets/img/pages/our-business-logistic/content-2.png" alt="Logistik" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Saat ini PT Hasnur Resources Terminal menjalankan bisnisnya di 2 lokasi, yaitu di Sungai Puting, Kabupaten Tapin, Kalimantan Selatan melalui pengoperasian 5 jetty dengan kapasitas terpasang 20 juta metrik ton (MT) per tahun dan Pendang, Kabupaten Barito Selatan, Kalimantan Tengah melalui pengoperasian 1 jetty dengan kapasitas terpasang 6 juta merik ton (MT) per tahun.</p>
<x-img src="/assets/img/pages/our-business-logistic/content-3.png" alt="Logistik" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
