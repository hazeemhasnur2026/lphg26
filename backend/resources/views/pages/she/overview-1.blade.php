@extends('layouts.app')

@section('title', 'Ikhtisar SHE')
@section('description', 'Direktorat Safety, Health & Environment (SHE) Hasnur Group.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Keselamatan, Kesehatan, & Lingkungan', 'url' => '/page/she/overview-1'],
            ['label' => 'Ikhtisar', 'url' => null]
        ]"
    title="Ikhtisar"
    description="Direktorat SHE bertujuan meminimalkan kecelakaan kerja serta menjaga keselamatan, kesehatan, dan lingkungan di seluruh unit usaha."
    :image="['src' => '/assets/img/pages/she-overview-1/banner.jpg', 'alt' => 'Ikhtisar, Hasnur Group']"
    :tabs="[
            ['label' => 'Ikhtisar', 'url' => '/page/she/overview-1', 'active' => true],
            ['label' => 'Aktivitas SHE', 'url' => '/page/she/she-activity', 'active' => false]
        ]" />

<section class="bg-slate-50 border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap gap-2">
    <a href="/page/she/overview-1" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-primary text-white">Ikhtisar</a><a href="/page/she/she-activity" class="px-5 py-2.5 rounded-full text-sm font-semibold transition bg-white text-slate-600 hover:text-primary border border-slate-200">Aktivitas SHE</a>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">DIREKTORAT SAFETY, HEALTH &amp; ENVIRONMENT (SHE)</h2>
<p class="reveal text-slate-600 leading-relaxed mb-4">Direktorat SHE Hasnur Group bertujuan untuk membantu meminimalkan terjadinya kecelakaan kerja, terjadinya kerugian atau kerusakan peralatan serta kerusakan lingkungan. Direktorat SHE memberikan dukungan kepada entitas/perusahaan dalam menegakkan Visi dan Misi menuju perusahaan yang peduli terhadap lingkungan dan masyarakat, dalam upaya pengelolaan sumber daya alam secara berkesinambungan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Kepedulian perusahaan terhadap aspek Keselamatan, Kesehatan Kerja dan Lingkungan dituangkan dengan jelas di dalam Kebijakan Keselamatan dan Kesehatan Kerja serta Pelestarian Lingkungan (K3L) yang ditandatangi oleh Presiden Direktur Hasnur Group, untuk menjadi salah satu acuan yang wajib dijalankan oleh seluruh Manajemen dan Karyawan sebagai pencapaian kinerja perusahaan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dalam menjalankan usaha dan operasional usaha di Strategic Business Unit (SBU) dan unit usaha (Entitas), Hasnur Group tetap berkomitmen mengikuti peraturan perundang-undangan yang berlaku dan selalu berupaya untuk mencapai target lebih baik terhadap aspek Keselamatan, Kesehatan Kerja dan Lingkungan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">AMDAL (Analisis Mengenai Dampak Lingkungan) dan UKL-UPL (Upaya Pengelolaan Lingkungan dan Upaya Pemantauan Lingkungan) merupakan instrumen pengelolaan lingkungan setiap usaha perusahaan dan syarat untuk mendapatkan Izin Lingkungan dari Pemerintah. Setiap usaha dan/atau kegiatan Perusahaan pun harus melalui kajian aspek K3 dan dampaknya terhadap lingkungan hidup.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dalam pelaksanaan pertambangan perusahaan menerapkan konsep Good Mining Practice (teknik pertambangan yang baik), menerapkan prinsip-prinsip kelestarian dalam pengelolaan hutan, menerapkan aspek keberlanjutan ( sustainability) pada pengelolaan agribisnis dan juga pada usaha-usaha lain yang dimiliki.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dengan upaya, kinerja yang baik dan berkelanjutan telah memberikan pencapaian bagi kinerja perusahaan diantaranya PROPER BIRU, Sertifikat International Organization for Standardization (ISO) 9001, Sertifikat ISO 14001, Sertifikat ISO 45001, Sertifikat Sistem Manajemen Keselamatan Pertambangan (SMKP), Sertifikat Indonesian Sustainable Palm Oil (ISPO), Sertifikat International Code for The Security of Ships and Port Facilities (ISPS CODE), dan Penghargaan Nihil Kecelakaan ( Zero Accident Award ) dari pemerintah.</p>
<x-img src="/assets/img/pages/she-overview-1/content-1.jpg" alt="Ikhtisar SHE" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<h2 class="reveal text-2xl font-extrabold text-primary mt-10 mb-4 first:mt-0">Logo SHE Hasnur Group</h2>
<x-img src="/assets/img/pages/she-overview-1/content-2.png" alt="Ikhtisar SHE" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Arti logo :</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Dua buah lambang gambar dengan siklus menyambung membentuk ilustrasi setetes air , melambangkan dinamika perusahaan dan peningkatan kualitas kegiatan operasional dan aspek K3L (Keselamatan, Kesehatan Kerja dan Lingkungan) secara berkelanjutan untuk kesejahteraan bersama.</li><li class="text-slate-600 leading-relaxed">Bidang lengkung berwarna merah , melambangkan seluruh kegiatan bisnis perusahaan yang senantiasa maju dan berkembang</li><li class="text-slate-600 leading-relaxed">Helai daun dengan tulang daun berwarna kuning dan ujung panah berwarna hijau , melambangkan budaya K3L dan kesejahteraan yang berkesinambungan seiring dengan beroperasinya perusahaan.</li><li class="text-slate-600 leading-relaxed">Semboyan berwarna biru , melambangkan kebulatan tekad dan komitmen perusahaan Hasnur Group terhadap K3L</li><li class="text-slate-600 leading-relaxed">Tulisan ‘safety first’ atau ‘aman’ , melambangkan selalu mengutamakan tindakan dan kondisi yang aman dan sehat dalam menjalankan usaha</li></ul>
<p class="reveal text-slate-600 leading-relaxed mb-4">Tulisan ‘go green’ atau ‘ramah lingkungan’ , melambangkan mewujudkan kelestarian lingkungan melalui produksi bersih dan meminimalkan dampak negatif yang ditimbulkan.</p>
  </div>
</section>
@endsection
