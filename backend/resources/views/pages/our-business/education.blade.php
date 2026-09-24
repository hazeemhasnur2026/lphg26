@extends('layouts.app')

@section('title', 'Pendidikan')
@section('description', 'SBU Pendidikan Hasnur Group, pendidikan bertaraf internasional dengan kearifan lokal.')

@section('content')
<x-page-hero
    :breadcrumb="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Bisnis Kami', 'url' => '/page/our-business/logistic'],
            ['label' => 'Pendidikan', 'url' => null]
        ]"
    title="Pendidikan"
    description="Mengembangkan pendidikan bertaraf internasional dengan pendekatan kearifan lokal dan religius."
    :image="['src' => '/assets/img/pages/our-business-education/banner.jpg', 'alt' => 'Pendidikan, Hasnur Group']"
    :tabs="[
            ['label' => 'Logistik', 'url' => '/page/our-business/logistic', 'active' => false],
            ['label' => 'Agribisnis & Kehutanan', 'url' => '/page/our-business/agribusiness-forestry', 'active' => false],
            ['label' => 'Energi', 'url' => '/page/our-business/energy', 'active' => false],
            ['label' => 'Teknologi & Jasa', 'url' => '/page/our-business/technology-services', 'active' => false],
            ['label' => 'Pendidikan', 'url' => '/page/our-business/education', 'active' => true],
            ['label' => 'Kebutuhan Pelanggan', 'url' => '/page/our-business/consumer', 'active' => false],
            ['label' => 'Investasi', 'url' => '/page/our-business/investment', 'active' => false]
        ]" />

<section class="py-16 sm:py-20">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="reveal text-slate-600 leading-relaxed mb-4">Hasnur Group melalui SBU Pendidikan berkomitmen mengembangkan Pendidikan bertaraf internasional dengan pendekatan kearifan lokal dan religious. Menciptakan sumberdaya manusia yang berkualitas dan mampu berdaya saing internasional.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Insan Pendidikan Indonesia (PT IPI) adalah perusahaan yang bergerak dalam penyedia layanan fasilitas pendidikan, bangunan pendidikan dan penyewaan gedung untuk kepentingan pendidikan. Sebagai penyedia layanan fasilitas pendidikan, PT IPI berkomitmen untuk mengembangkan pendidikan dengan menciptakan bangunan pendidikan yang ramah siswa dengan berbagai fasilitas untuk menunjang pendidikan siswa. Dengan fokus penyedia layanan kepada tingkat pendidikan kelompok bermain (KB), taman kanak-kanak (TK), sekolah dasar (SD), sekolah menengah pertama (SMP), dan sekolah menengah atas (SMA). PT IPI berupaya untuk memberikan kontribusi positif terhadap pembangunan fasilitas pendidikan yang berkelanjutan dan efisien di seluruh negeri.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Beberapa lini bisnis dari PT IPI adalah sebagai berikut:</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Penyediaan Layanan Fasilitas Pendidikan</li><li class="text-slate-600 leading-relaxed">Penyewaan lahan dan bangunan untuk penunjang pendidikan</li></ul>
<p class="reveal text-slate-600 leading-relaxed mb-4">Melalui sekolah Al-Hayyan Excellent School, PT IPI berkomintmen untuk memberikan gedung penunjang pendidikan dan fasilitas pendidikan berkualitas untuk meningkatkan kualitas pendidikan di Indonesia.</p>
<x-img src="/assets/img/pages/our-business-education/content-1.jpg" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">CODERO dibawah PT PENGEMBANGAN INOVASI PENGAJARAN adalah sebuah lembaga pelatihan Coding dan Robot untuk mendorong pengembangan kreativitas, berpikir kritis dan penguasaan teknologi sejak usia dini, dengan berbagai program yang bisa diaplikasikan dalam kegiatan Intrakurikuler maupun Ekstrakurikuler di Sekolah serta kursus di center dan perwakilan Codero.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Saat ini Codero memiliki Center dan perwakilan di beberapa kota diantaranya di Jakarta, Tangerang Selatan, Banjarmasin, Yogyakarta dan Jayapura.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Codero menawarkan kurikulum inovatif dan berkelas yang dirancang untuk merangsang pikiran dan memperkaya pembelajaran. Program Codero Saat ini</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Regular Class Coding</li><li class="text-slate-600 leading-relaxed">Regular Class Robotic</li><li class="text-slate-600 leading-relaxed">Private Class Coding</li><li class="text-slate-600 leading-relaxed">Private Class Robotic</li><li class="text-slate-600 leading-relaxed">Ekstrakurikuler/ Intrakurikuler Sekolah</li><li class="text-slate-600 leading-relaxed">Holiday Program</li></ul>
<x-img src="/assets/img/pages/our-business-education/content-2.jpg" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Bagaimana Cara Codero Mengajar ?</p>
<ul class="reveal list-disc pl-5 space-y-2 mb-6"><li class="text-slate-600 leading-relaxed">Bahasa Bilingual : Kami Mengajar menggunakan Bahasa Inggris dan Indonesia</li><li class="text-slate-600 leading-relaxed">Terdapat Certificate &amp; Report yang akan didapat Siswa per semester</li><li class="text-slate-600 leading-relaxed">Curriculum, Mengedepankan metode STEAM(Science, Technology, Engineering, Art and Math) + 4C - Creativity (kreatifitas), Critical thinking (berpikir kritis), Communication (komunikasi), dan Colaboration (kolaborasi).</li><li class="text-slate-600 leading-relaxed">Project Based : Setiap Pelajaran kami setiap sesi Kelas Berbasis project yang berbeda beda</li><li class="text-slate-600 leading-relaxed">Enticing Class : Pengalaman “Bermain &amp; Belajar”</li></ul>
<x-img src="/assets/img/pages/our-business-education/content-3.jpg" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Dengan pendekatan yang menyenangkan dan interaktif, Codero ( www.codero.id ) menciptakan lingkungan belajar yang memotivasi anak-anak untuk tumbuh dan mengenal dunia teknologi secara menyeluruh. Program belajar mencakup berbagai tingkatan, dimulai dari coding dasar hingga pengembangan aplikasi game, robotika, Arduino dan Digital Art. Codero juga menyelenggarakan berbagai acara dan kompetisi, seperti Cyber Code Competition, Codero Roblox Championship dll untuk menguji keterampilan dan kemampuan yang sudah diperoleh anak-anak serta mendorong semangat berprestasi.</p>
<x-img src="/assets/img/pages/our-business-education/content-4.jpg" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">Codero berkomitmen untuk memberikan dampak positif dalam dunia pendidikan dengan memberikan wawasan dan keterampilan yang mempersiapkan anak-anak untuk masa depan yang penuh dengan teknologi. Dengan tagline "Building Future Innovators in Coding &amp; Robotic," Codero terus berusaha menciptakan generasi penerus yang kreatif, berdaya saing, dan siap menghadapi perubahan dalam era digital.</p>
<x-img src="/assets/img/pages/our-business-education/content-5.jpg" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Inovasi Ekosistem Pendidikan sebagai entitas SBU Education Hasnur Group Mengembangkan sebuah platform untuk meningkatkan kompetensi guru berbasis LMS (Learning Management System) yang fokus pada penyediaan layanan pelatihan guru yang berkualitas dan mudah digunakan bagi masyarakat Indonesia. PT Inovasi Ekosistem Pendidikan memiliki 2 produk utama yaitu Guru Inovatif (https://guruinovatif.id) dan Kampus Inovatif (https://kampusinovatif.id). Guru Inovatif menyediakan berbagai macam pelatihan Guru mulai dari pelatihan keterampilan digital, pelatihan keguruan, dan pelatihan kepemimpinan dalam bentuk kursus online, event online, offline, dan juga workshop.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Selain itu PT Inovasi Ekosistem Pendidikan juga menyediakan berbagai macam sumber daya pendukung pembelajaran seperti Modul Pembelajaran, Media Pembelajaran, artikel, dan Video Pembelajaran. Selain Guru Inovatif, PT Inovasi Ekosistem Pendidikan juga mempunyai Kampus Inovatif yang bertujuan untuk mendorong peningkatan kualitas dan nilai akademik mahasiswa di Indonesia, melalui: praktisi mengajar, learning program, KI Goes to Kampus, Dan digital Konten.</p>
<x-img src="/assets/img/pages/our-business-education/content-6.png" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Inovasi Ekosistem Pendidikan berlokasi di Yogyakarta telah berpengalaman bekerjasama dengan berbagai kampus baik negeri maupun swasta di Indonesia seperti Universitas Nahdhatul Ulama , Sarjanawiyata Tamansiswa, Universitas Negeri Malang, Universitas Negeri Jakarta dan banyak lagi kampus-kampus yang telah bekerjasama dengan PT Inovasi Ekosistem Pendidikan.</p>
<x-img src="/assets/img/pages/our-business-education/content-7.jpg" alt="Pendidikan" class="reveal w-full h-auto rounded-2xl shadow-lg my-8" loading="lazy" sizes="(min-width: 768px) 768px, 100vw" />
<p class="reveal text-slate-600 leading-relaxed mb-4">PT Cipta Daya inovasi adalah sebuah perusahaan rintisan yang memiliki orientasi bisnis dalam pengembangan inovasi digital untuk menjawab berbagai permasalahan di masyarakat. Sebagai bagian dari entitas SBU Education Hasnur Group, PT. Cipta Daya Inovasi tengah mengembangkan produk terbarunya, yaitu Hulu Talent (HR Software), lebih spesifik berfokus pada Performance Management System (PMS) yang difokuskan untuk mengelola kinerja karyawan atau perusahaan guna mencapai tujuan yang telah ditetapkan.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Performance Management System ini bertujuan untuk meningkatkan kinerja secara maksimal, memberikan umpan balik yang efektif, memperkuat komunikasi dua arah, dan mendorong pertumbuhan serta pengembangan karyawan atau perusahaan. Selain itu, sistem ini membantu dalam mengidentifikasi masalah kinerja, menetapkan kebijakan kenaikan gaji, kompensasi, atau promosi, sekaligus membentuk budaya kerja yang positif dan produktif.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Hulu Talent menawarkan sejumlah produk utama, termasuk Employee Database, Key Performance Indicators, Hierarchy, Talent Mapping, Assessment, Project Management, Humanity Approach, 9 Boxes, dan Artificial Intelligence Approach.</p>
<p class="reveal text-slate-600 leading-relaxed mb-4">Kantor PT. Cipta Daya Inovasi dan Hulu Talent berlokasi di Banjarmasin, dengan dua kantor yang berbeda. Kantor pertama berada di GIBS, Jl. Trans Kalimantan, Sungai Lumbah, Alalak, Barito Kuala, Kalimantan Selatan, Indonesia 70582. Sementara itu, kantor kedua terletak di Wetland Square, Jl. A. Yani No.KM 3,5, Kebun Bunga, Kec. Banjarmasin Tim., Kota Banjarmasin, Kalimantan Selatan 70237.</p>

    <x-sbu-website-link :unit="$unit ?? null" />
  </div>
</section>
@endsection
