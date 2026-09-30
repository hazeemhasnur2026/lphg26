<?php

/*
 * Navigation structure for the article listings.
 *
 * Breadcrumbs, tab strips and banners all read from here, so a category's
 * label is written once rather than repeated in the controller, the view and
 * the breadcrumb. Mirrors the groups in tools/build_page_hero.py, which does
 * the same job for the fixed-content pages.
 */
return [
    /*
     * Masa simpan pesan dari formulir Hubungi Kami, dalam bulan.
     *
     * Menyimpan data pribadi tanpa batas waktu bertentangan dengan prinsip
     * pemrosesan terbatas. Angka 24 bulan adalah nilai awal yang masuk akal
     * untuk korespondensi bisnis, bukan keputusan yang sudah dikonfirmasi
     * legal; ubah lewat CONTACT_RETENTION_MONTHS di .env setelah disepakati.
     *
     * Diisi 0 berarti pemangkasan dimatikan dan pesan disimpan selamanya.
     */
    'retensi_pesan_bulan' => (int) env('CONTACT_RETENTION_MONTHS', 24),

    /*
     * Berapa versi terakhir yang disimpan per artikel.
     *
     * Dibatasi per jumlah, bukan per umur. Umur adalah ukuran yang salah di
     * sini: artikel yang disunting sepuluh kali dalam sebulan akan kehilangan
     * seluruh riwayatnya sekaligus begitu bulan itu lewat, sedangkan artikel
     * yang disunting sekali setahun menyimpan satu versi selamanya. Yang ingin
     * dijawab riwayat versi adalah "apa isinya sebelum beberapa suntingan
     * terakhir", dan itu pertanyaan tentang jumlah.
     *
     * Lima puluh cukup panjang untuk menutupi hampir setiap penyuntingan yang
     * masih diingat orang, dan setiap versi menyimpan satu salinan penuh badan
     * artikel — tanpa batas, tabelnya tumbuh selamanya tanpa ada yang melihat.
     *
     * Diisi 0 berarti tidak ada pemangkasan.
     */
    'retensi_revisi' => (int) env('REVISION_RETENTION', 50),

    /*
     * Footer panel admin.
     *
     * Tautan dukungan dibuat dapat diatur lewat .env, bukan ditulis tetap di
     * view: alamat tim yang menangani panel ini berubah lebih sering daripada
     * kodenya, dan perubahan alamat surel tidak layak menuntut deploy.
     */
    'panel' => [
        'pemilik' => env('PANEL_OWNER', 'HASNUR GROUP — MANAGEMENT INFORMATION SYSTEMS & TECHNOLOGY'),
        'dukungan' => env('PANEL_SUPPORT_URL', 'mailto:mist@hasnurgroup.com'),
        'privasi' => env('PANEL_PRIVACY_URL', '/page/legal/kebijakan-privasi'),
    ],

    /*
     * Masa simpan peristiwa analitik, dalam bulan.
     *
     * Dibatasi per umur, kebalikan dari revisi di atas, karena pertanyaannya
     * memang tentang waktu: papan analitik membandingkan bulan ini dengan bulan
     * lalu dan tidak pernah menanyakan apa pun tentang tahun lalu. Dua belas
     * bulan cukup untuk satu perbandingan tahun-ke-tahun.
     *
     * Tabelnya bertambah satu baris tiap klik, jadi ia yang tumbuh paling cepat
     * di basis data ini dan ia yang paling cepat mendominasi backup harian.
     *
     * Diisi 0 berarti tidak ada pemangkasan.
     */
    'retensi_analitik_bulan' => (int) env('ANALYTICS_RETENTION_MONTHS', 12),

    /*
     * Halaman berisi konten tetap: URL -> view. Router dan sitemap membaca
     * daftar yang sama, jadi menambah halaman berarti menambah satu baris di
     * sini dan satu view. Tidak ada dua salinan yang bisa menyimpang.
     */
    'pages' => [
        'about-us/overview' => 'pages.about-us.overview',
        'about-us/vision-mission' => 'pages.about-us.vision-mission',
        'csr/csr-overview' => 'pages.csr.csr-overview',
        'she/overview-1' => 'pages.she.overview-1',
        'our-business/logistic' => 'pages.our-business.logistic',
        'our-business/agribusiness-forestry' => 'pages.our-business.agribusiness-forestry',
        'our-business/energy' => 'pages.our-business.energy',
        'our-business/technology-services' => 'pages.our-business.technology-services',
        'our-business/education' => 'pages.our-business.education',
        'our-business/consumer' => 'pages.our-business.consumer',
        'our-business/investment' => 'pages.our-business.investment',
        'publication/multimedia' => 'pages.publication.multimedia',
        'publication/gallery' => 'pages.publication.gallery',
        'career/our-vacancy' => 'pages.career.our-vacancy',
        'legal/kebijakan-privasi' => 'pages.legal.kebijakan-privasi',
    ],

    /*
     * URL kanal resmi Hasnur Group, satu sumber untuk ketiga tempat yang
     * memakainya: ikon di footer, skema Organization/sameAs untuk SEO
     * (app/Support/StructuredData.php), dan data contoh analitik. Sebelum ini
     * ketiganya menulis ulang URL yang sama, dan salah satu sudah mulai
     * berselisih — SeedAnalyticsSample.php menulis "hasnur-group" dengan tanda
     * hubung, dua tempat lain tanpa.
     */
    'sosial' => [
        'linkedin' => 'https://www.linkedin.com/company/hasnurgroup',
        'instagram' => 'https://www.instagram.com/hasnurgroup',
        'youtube' => 'https://www.youtube.com/@hasnurgroup',
    ],

    /*
     * Slug unit bisnis tidak selalu sama dengan kunci terjemahan navigasinya:
     * "agribusiness-forestry" memendek jadi "agribusiness", "technology-services"
     * jadi "technology". Dipetakan eksplisit di sini, satu tempat, alih-alih
     * ditebak di view lewat penggabungan string — begitu unit bisnis baru
     * ditambahkan lewat panel dan lupa didaftarkan di sini, menu navigasi jatuh
     * ke $unit->name (bahasa Indonesia di ketiga versi bahasa) daripada diam-diam
     * salah menunjuk kunci terjemahan unit lain.
     */
    'business_unit_nav_keys' => [
        'logistic' => 'nav.business.logistic',
        'agribusiness-forestry' => 'nav.business.agribusiness',
        'energy' => 'nav.business.energy',
        'technology-services' => 'nav.business.technology',
        'education' => 'nav.business.education',
        'consumer' => 'nav.business.consumer',
        'investment' => 'nav.business.investment',
    ],

    'listings' => [
        'latest-update' => [
            'label' => 'Update Terbaru', 'section' => 'publication',
            'group' => 'Publikasi', 'banner' => 'publication-latest-update',
        ],
        'news' => [
            'label' => 'Berita', 'section' => 'publication',
            'group' => 'Publikasi', 'banner' => 'publication-news',
        ],
        'press-release' => [
            'label' => 'Press Release', 'section' => 'publication',
            'group' => 'Publikasi', 'banner' => 'publication-press-release',
        ],
        'csr-activity' => [
            'label' => 'Aktifitas CSR', 'section' => 'csr',
            'group' => 'Tanggung Jawab Sosial Perusahaan', 'banner' => 'csr-csr-activity',
        ],
        'she-activity' => [
            'label' => 'Aktivitas SHE', 'section' => 'she',
            'group' => 'Keselamatan, Kesehatan, & Lingkungan', 'banner' => 'she-she-activity',
        ],
    ],

    // Sibling pages shown as the tab strip, keyed by section.
    'siblings' => [
        'publication' => [
            ['label' => 'Update Terbaru', 'url' => '/page/publication/latest-update'],
            ['label' => 'Berita', 'url' => '/page/publication/news'],
            ['label' => 'Press Release', 'url' => '/page/publication/press-release'],
            ['label' => 'Multimedia', 'url' => '/page/publication/multimedia'],
            ['label' => 'Galeri', 'url' => '/page/publication/gallery'],
        ],
        'csr' => [
            ['label' => 'Ikhtisar', 'url' => '/page/csr/csr-overview'],
            ['label' => 'Aktifitas CSR', 'url' => '/page/csr/csr-activity'],
        ],
        'she' => [
            ['label' => 'Ikhtisar', 'url' => '/page/she/overview-1'],
            ['label' => 'Aktivitas SHE', 'url' => '/page/she/she-activity'],
        ],
    ],
];
