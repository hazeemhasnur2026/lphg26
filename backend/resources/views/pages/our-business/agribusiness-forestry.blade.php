@extends('layouts.app')

@section('title', 'Agribisnis & Kehutanan')
@section('description', 'SBU Agribisnis & Kehutanan Hasnur Group, kehutanan dan perkebunan kelapa sawit.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Agribisnis & Kehutanan', 'url' => null]
        ]"
    title="Agribisnis &amp; Kehutanan"
    description="Berawal dari jasa penarikan rakit kayu di DAS Barito, kini mencakup usaha perkebunan dan kehutanan."
    :image="['src' => '/assets/img/pages/our-business-agribusiness-forestry/banner.png', 'alt' => 'Agribisnis & Kehutanan, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => false],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => true],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => false],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => false],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => false],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => false]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group memiliki cukup pengalaman dalam lini usaha kehutanan dan perkebunan. Diawali dengan usaha jasa penarikan rakit (kayu bulat/log) di Wilayah DAS Barito, kemudian berkembang hingga memiliki areal Hak Pengusahaan Hutan sendiri (istilah HPH sekarang berubah menjadi Perizinan Berusaha Pemanfaatan Hutan – PBPH) PT Barito Putera dan PT Hasnur Jaya Utama dan PT Nusantara Alam Raya Sejahtera. Untuk usaha perkebunan, Hasnur Group memiliki portofolio lini usaha perkebunan kelapa sawit melalui anak perusahaannya, yaitu PT Hasnur Citra Terpadu dan PT Barito Putera Plantation.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Dalam menjalankan usahanya, PT Barito Putera memiliki Izin Usaha Pemanfaatan Hasil Hutan Kayu dalam Hutan Alam (IUPHHK-HA) pada areal seluas 42.065 Hektar. Lokasi terletak di Kabupaten Barito Utara (Kelompok Hutan Sei Lahei) dan Kabupaten Murung Raya (Kelompok Hutan Sungai Laung), Provinsi Kalimantan Tengah. Demikian pula halnya dengan PT Hasnur Jaya Utama memiliki Izin Usaha Pemanfaatan Hasil Hutan Kayu dalam Hutan Alam (IUPHHK-HA) dengan luas areal 30.825 Hektar. Areal berada di wilayah Kabupaten Barito Selatan dan Kabupaten Barito Timur, Provinsi Kalimantan Tengah.</p>
<x-img src="/assets/img/pages/our-business-agribusiness-forestry/content-1.png" alt="Agribisnis & Kehutanan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Pada perkembangannya PT Nusantara Alam Raya Sejahtera (NAS) menambah usaha untuk SBU Forestry menjadi empat usaha kegiatan PBPH seluas 133.040 Hektar. Secara kebetulan lokasi konsesi PT NAS sendiri juga berada di Kabupaten Murung Raya. Dari empat lokasi kegiatan PBPH seluruhnya menggunakan Daerah Aliran Sungai (DAS) Barito untuk pemiliran atau jalur transport sampai dengan Banjarmasin. Saat ini pengembangan usaha yang terintegrasi bersama sama dengan PT Wana Agro Selaras (WAS) dan PT Barito Putera Industri (BPI) membentuk skema Bisnis Terintegrasi Forestry yang di dalamnya terdapat HPH pada PT NAS, Kontraktor pada PT WAS dan Industri Veneer pada PT BPI.  dengan pengelolaan hasil hutan secara optimal yang memberikan nilai tambah dan tepat guna agar bisnis berkelanjutan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group dalam menjalankan kegiatan usahanya, senantiasa meningkatkan peran serta dan kontribusi masyarakat sekitar hutan yang berkomitmen memelihara lingkungan dan kekayaan hutan secara lestari dan berkelanjutan. Saat ini pengembangan usaha yang terintegrasi bersama-sama dengan PT Wana Agro Selaras (WAS) dan PT Barito Putera Industri (BPI) membentuk skema Bisnis Terintegrasi Forestry yang di dalamnya terdapat HPH pada PT NAS, Kontraktor pada PT WAS dan Industri Veneer pada PT BPI.  Dengan pengelolaan hasil hutan secara optimal dipercaya dapat memberikan nilai tambah dan tepat guna agar bisnis forestry dapat berjalan secara berkelanjutan.</p>
<x-img src="/assets/img/pages/our-business-agribusiness-forestry/content-2.png" alt="Agribisnis & Kehutanan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Pada sektor Perkebunan, PT Hasnur Citra Terpadu memiliki areal perkebunan kelapa sawit seluas 9.652 Ha yang berlokasi di Kabupaten Tapin Provinsi Kalimantan Selatan sedangkan PT Barito Putera Plantation memiliki areal perkebunan kelapa sawit seluas 13.006 Ha yang berlokasi di Kabupaten Barito Kuala Provinsi Kalimantan Selatan. Dalam pembangunan kebun kelapa sawit, PT Hasnur Citra Terpadu dan PT Barito Putera Plantation menerapkan teknik budidaya yang tepat dan sesuai dengan ekosistem yang ada, serta berhasil menghijaukan lahan tidur yang kurang produktif. Selain menggunakan sistem angkutan darat, kedua perusahaan juga menggunakan angkutan air, sehingga tetap menjaga kelestarian ekosistem dan lingkungan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Pada Tahun 2012 Gubernur Kalimantan Selatan memberikan Piagam Penghargaan kepada PT Hasnur Citra Terpadu atas terlaksananya Program Kemitraan Pembangunan Kebun Kelapa Sawit di Kalimantan Selatan sesuai dengan Permentan No. 26/2007, sebagai implementasi kewajiban perusahaan perkebunan untuk mensejahterakan masyarakat di sekitar kebun kelapa sawit.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Citra Terpadu berkembang menjadi perusahaan perkebunan kelapa sawit yang disegani di wilayah Kalimantan Selatan, dan pada Februari 2013 lalu, PT Hasnur Citra Terpadu meresmikan pabrik crude palm oil (CPO) pertama di Kabupaten Tapin, Kalimantan Selatan. Pabrik minyak sawit mentah (CPO) memiliki kapasitas produksi 45ton tandan buah segar (TBS) per jam, yang dapat ditingkatkan menjadi 90 ton TBS per jam.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Citra Terpadu juga telah menerima sertifikat di ISPO ( Indonesia Sustainable Palm Oil ) pada 2016, dan terus berkomitmen penuh untuk mentaati prinsip dan kriteria ISPO untuk produksi minyak kelapa sawit secara berkelanjutan.</p>
<x-img src="/assets/img/pages/our-business-agribusiness-forestry/content-3.png" alt="Agribisnis & Kehutanan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Hasnur Citra Terpadu dan PT Barito Putera Plantation, terbuka untuk bekerjasama dengan pihak lainnya, antara lain dalam pemanfaatan limbah dari hasil produksi kelapa sawit baik menjadi energi terbarukan maupun kegunaan lain yang besar manfaatnya di masyarakat. Dalam menjalankan kegiatan usahanya, Hasnur Group melalui usaha perkebunan kelapa sawit juga mengedepankan pemberdayaan masyarakat secara berkelanjutan sebagai wujud kepedulian perusahaan terhadap lingkungan sekitar usaha. PT Hasnur Citra Terpadu dan PT Barito Putera Plantation berkomitmen untuk membangun masa depan dan berkembang bersama masyarakat.</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
