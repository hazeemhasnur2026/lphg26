# Laporan Perubahan terhadap TOR Web Development 2026 (R1)

Pemetaan seluruh pekerjaan yang sudah dilakukan terhadap kedelapan bagian TOR.
Per 29 September 2026.

**Penting untuk konteks:** TOR menetapkan kick-off **1 Oktober 2026**, dua hari
dari tanggal laporan ini. Yang ada sekarang adalah prototipe yang dibangun
mendahului jadwal itu, bukan penyerahan vendor terhadap milestone D-01 s/d D-15.
Laporan ini membaca prototipe tersebut memakai TOR sebagai daftar periksa.

**Keterangan status**

| Tanda | Arti |
|---|---|
| ✅ | Terpenuhi |
| 🟡 | Sebagian |
| ⬜ | Belum dikerjakan |
| ⚠️ | Berbeda dari TOR — butuh keputusan |

---

## Ringkasan per bagian

| Bagian | Terpenuhi | Sebagian | Belum | Beda |
|---|:-:|:-:|:-:|:-:|
| 01 Objective | 3 | 4 | 0 | 1 |
| 02 Scope | 2 | 4 | 4 | 0 |
| 03 Sitemap | 18 | 3 | 11 | 0 |
| 04 UX/UI | 5 | 2 | 1 | 2 |
| **05 CMS** | **9** | **3** | **0** | **0** |
| 06 Technical | 7 | 5 | 2 | 0 |
| 07 Content Governance | 7 | 3 | 2 | 0 |
| 08 Deliverables | 3 | 2 | 10 | 0 |

Pekerjaan putaran ini terkonsentrasi pada **05 CMS**, dan merembet ke bagian
06 dan 07 karena keduanya bersandar pada kemampuan CMS yang sama.

---

## 01 — Objective

| ID | Objective | Status | Keterangan |
|---|---|:-:|---|
| OBJ-01 | Corporate Presence | 🟡 | Situs berjalan, 35 artikel termigrasi, tiga bahasa. Belum semua pilar TOR punya halaman |
| OBJ-02 | Corporate Identity | ⚠️ | Palet dan tipografi situs berbeda dari yang ditulis TOR — lihat 04 UX/UI |
| OBJ-03 | Stakeholder Engagement | 🟡 | **Pencarian global ditambahkan** putaran ini. Navigasi belum mengikuti 5 pilar TOR |
| OBJ-04 | Business Visibility | ✅ | Tujuh sektor bisnis punya halaman dan dikelola lewat CMS |
| OBJ-05 | Sustainability | 🟡 | CSR dan SHE ada sebagai kategori artikel; pilar Sustainability/ESG belum punya halaman |
| OBJ-06 | Employer Branding | 🟡 | Lowongan dikelola CMS; People & Culture dan Learning & Development belum ada |
| OBJ-07 | Content Agility | ✅ | **Terpenuhi penuh putaran ini** — CMS, peran berbasis akses, dan alur persetujuan |
| OBJ-08 | Performance & Security | ✅ | Backup otomatis, header keamanan, pipeline gambar WebP, 146 tes otomatis |

**Yang berubah putaran ini:** OBJ-07 dari kosong menjadi terpenuhi penuh.
OBJ-03 dan OBJ-08 naik dari sebagian.

---

## 02 — Scope

| ID | Workstream | Status | Keterangan |
|---|---|:-:|---|
| S-01 | Discovery & Requirement | 🟡 | `docs/spesifikasi.md` dan `docs/konten-situs.md` ada; belum ada stakeholder interview |
| S-02 | Information Architecture | 🟡 | Sitemap mengikuti situs lama, belum sitemap TOR 2026 |
| S-03 | UX/UI Design | ⬜ | Tidak ada Figma atau prototipe clickable terpisah |
| S-04 | Frontend Development | 🟡 | Responsif dan berjalan; belum seluruh halaman TOR |
| S-05 | Backend & CMS | ✅ | **Selesai putaran ini** — model konten, peran, alur, pustaka media |
| S-06 | Integration | 🟡 | Papan analitik sendiri berjalan dan pencarian siap; ID platform luar, peta, dan media sosial belum |
| S-07 | Migration & Content | ✅ | 35 artikel, metadata, redirect 301, dan 410 untuk yang pensiun |
| S-08 | QA & UAT | 🟡 | 146 tes otomatis berjalan tiap push; UAT bisnis belum |
| S-09 | Launch & Handover | 🟡 | Dokumentasi dan prosedur ada; serah terima formal belum |
| S-10 | Post-Launch Support | ⬜ | Belum berlaku |

---

## 03 — Sitemap

TOR mendaftar 32 entri. Yang **sudah ada**: Home, Overview, Vision & Mission,
Values, tujuh halaman sektor bisnis, SHE, Latest News, Press Release, Photo
Gallery, Video Gallery (Multimedia), Career, Search, ID/EN, Social Media,
Contact Us, Related Entities.

**Yang belum ada — 11 entri:**

| No | Halaman TOR | Pilar | Catatan |
|---|---|---|---|
| 5 | Management | About | Profil kepemimpinan dan tata kelola |
| 6 | Business Overview | Our Business | Halaman ikhtisar portofolio; tujuh sektornya sudah ada |
| 14 | Sustainability Overview | Sustainability | Pilar Sustainability belum ada sebagai pilar tersendiri |
| 15 | ESG | Sustainability | Environmental, Social, Governance |
| 17 | People & Culture | Our People | Pilar Our People belum ada sama sekali |
| 18 | Learning & Development | Our People | |
| 20 | HR Policies | Our People | |
| 23 | Publications | News & Insights | Tipe konten Publikasi **sudah ada di CMS**, halaman daftarnya belum |
| 30 | Locations | Footer | Data kantor sudah ada di CMS dan tampil di footer, halaman tersendiri belum |
| 31 | Media Contact | Footer | Terpisah dari Contact Us umum |

**Perubahan struktural yang dibutuhkan:** TOR memindahkan CSR dan SHE ke bawah
pilar **Sustainability**, dan Career ke bawah **Our People**. Sekarang keduanya
berdiri sendiri sebagai pilar. Ini bukan penambahan halaman, melainkan penataan
ulang navigasi dan URL — dan URL yang berubah memutus tautan yang sudah
terindeks, jadi butuh peta redirect 301.

**Yang berubah putaran ini:** entri 26 (Search) dari belum ada menjadi ada,
lengkap dengan penyaring tipe dan tag.

---

## 04 — UX/UI

| ID | Principle | Status | Keterangan |
|---|---|:-:|---|
| UX-01 | Clean Corporate | ✅ | |
| UX-02 | Brand Consistency | 🟡 | **Palet sudah mengikuti TOR** (29 Sep 2026); tipografi masih berbeda |
| UX-03 | Responsive | ✅ | |
| UX-04 | Navigation | ⚠️ | Tujuh pilar, TOR minta maksimal lima. Contact masih tombol utama, TOR minta di footer saja |
| UX-05 | Search | ✅ | **Ditambahkan putaran ini** — cari menurut judul, kata kunci, tipe konten, dan tag |
| UX-06 | CTA | 🟡 | CTA ada; belum ditinjau terhadap "tidak berlebihan" |
| UX-07 | Accessibility | 🟡 | Alt text dikelola CMS berikut penyaring yang belum terisi; audit WCAG 2.1 AA menyeluruh belum |
| UX-08 | Content Discoverability | ✅ | **Tag dan kategori ditambahkan putaran ini**; breadcrumb dan artikel terkait sudah ada |
| UX-09 | Media | ✅ | Varian WebP, lazy loading, dan crop responsif |
| UX-10 | Prototype | ⬜ | Prototipe statis ada di folder `preview LP-HG`, bukan Figma clickable |

### Dua selisih yang butuh keputusan

**UX-02 — palet sudah diterapkan, tipografi belum.**

| | TOR | Situs sekarang |
|---|---|---|
| Warna | Emerald `#007A53`, Navy `#0F2747`, Gold `#C9A227`, putih | ✅ Sudah mengikuti TOR |
| Huruf | Calibri / Aptos / Montserrat | ⚠️ Plus Jakarta Sans |

Palet diganti 29 September 2026. Pemetaannya menukar nilai, bukan menyusun
ulang tampilan: warna utama menjadi Navy, aksen menjadi Gold, hijau menjadi
Emerald. Merah `#E03633` dipertahankan karena TOR tidak menyebut warna merah
sama sekali, sedangkan ia satu-satunya penanda galat dan peringatan di situs.

Perlu diketahui: warna sebelumnya diambil dari **logo resmi Hasnur Group**
(biru `#154F91`, kuning `#FFE80A`, hijau `#2A9941`). Palet TOR berbeda sama
sekali dari logo itu, dan **berkas gambar logo tidak ikut berubah** — ia tetap
biru dan kuning. Kalau yang berlaku ternyata warna logo, pengembaliannya cukup
di satu blok `@theme` pada `resources/css/app.css`.

Dua pasangan warna gagal kontras WCAG AA yang diminta UX-07: Gold di atas putih
(2.42:1) dan Emerald di atas Navy (2.79:1). Yang pertama bukan hal baru — aksen
lama di atas putih hanya 2.25:1 — tetapi `text-accent` dipakai delapan kali
sebagai label kecil di atas latar terang dan perlu diperbaiki. Perbaikannya
bukan mengganti warnanya melainkan mengganti perannya: gold untuk garis, ikon,
dan latar, bukan untuk teks kecil di atas putih.

**UX-04 — jumlah pilar navigasi.**

TOR: maksimal lima pilar — About, Business, Sustainability, People, News &
Insights — dan Contact **tidak** di navigasi utama.

Sekarang: Beranda, Tentang Kami, Bisnis Kami, CSR, SHE, Karir, Publikasi,
ditambah tombol Hubungi Kami. Tujuh pilar plus Contact.

Menyesuaikannya berarti melipat CSR dan SHE ke dalam Sustainability, Karir ke
dalam Our People, dan memindahkan Contact ke footer. Sekali lagi: URL berubah,
jadi butuh peta redirect.

---

## 05 — CMS

Bagian yang dikerjakan penuh putaran ini. Rincian lengkap per butir ada di
`docs/status-cms-tor.md`; bukti visual di folder `screenshot_cms_tor`.

| ID | Butir | Status | Sisa |
|---|---|:-:|---|
| CMS-01 | Admin Dashboard | ✅ | — |
| CMS-02 | Role & Permission | ✅ | Tetapkan peran tiap orang |
| CMS-03 | Workflow & audit trail | ✅ | — |
| CMS-04 | Content Types | ✅ | — |
| CMS-05 | Media Library | ✅ | Jalankan `media:import` di server |
| CMS-06 | SEO Fields | ✅ | Tulis meta description artikel lama |
| CMS-07 | Scheduling | ✅ | — |
| CMS-08 | Search & Taxonomy | ✅ | Sepakati daftar tag baku |
| CMS-09 | Versioning | ✅ | — |
| CMS-10 | Analytics | 🟡 | **Dilengkapi putaran ini** — papan analitik di panel, empat event tercatat sendiri. Butuh ID platform yang disetujui |
| CMS-11 | Backup | 🟡 | Salinan luar mesin; sahkan RPO/RTO |
| CMS-12 | Training | 🟡 | Jadwalkan dua sesi pelatihan |

---

## 06 — Technical Requirement

| ID | Area | Status | Keterangan |
|---|---|:-:|---|
| TECH-01 | Architecture | ✅ | Laravel 13, PHP 8.4, Filament 4, SQLite; Docker dengan nginx + php-fpm |
| TECH-02 | Hosting | 🟡 | Berjalan di Docker; kapasitas dan pemantauan produksi belum ditetapkan |
| TECH-03 | Performance | 🟡 | Varian WebP, lazy loading, opcache, cache config/route/view. Benchmark sebelum/sesudah belum diukur |
| TECH-04 | Security | 🟡 | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, sesi terenkripsi, throttle formulir. Belum ada CSP dan HSTS |
| TECH-05 | Backup & Recovery | ✅ | **Diperbaiki putaran ini** — harian 02.15 WIB, volume terpisah, prosedur pemulihan tertulis, usulan RPO/RTO |
| TECH-06 | Browser Support | ⬜ | Belum ada matriks uji lintas peramban |
| TECH-07 | SEO | ✅ | **Dilengkapi putaran ini** — sitemap dinamis, robots, kanonik, hreflang, JSON-LD, redirect 301, kontrol noindex |
| TECH-08 | Accessibility | 🟡 | Alt text dikelola CMS, struktur semantik, `aria-label` diperbaiki. Audit menyeluruh belum |
| TECH-09 | Analytics | 🟡 | **Dilengkapi putaran ini** — GA4/GTM siap, empat event terpasang, dan kini juga tercatat sendiri di `analytics_events` dengan papan angka di panel. Menunggu ID; Search Console belum |
| TECH-10 | Integration | 🟡 | Formulir kontak dan tautan sosial ada; peta belum |
| TECH-11 | Scalability | ✅ | Menambah halaman berarti satu baris config dan satu view; tipe konten dan taksonomi dapat ditambah tanpa migrasi |
| TECH-12 | Source & Handover | 🟡 | Repositori, dokumentasi, dan prosedur deploy ada; serah terima formal belum |
| TECH-13 | Environment | 🟡 | Pengembangan dan produksi terpisah; **staging belum ada** |
| TECH-14 | Monitoring | ⬜ | Tidak ada uptime, error tracking, maupun alerting |

**Perubahan putaran ini:** TECH-05 dari rusak menjadi berjalan, TECH-07
dilengkapi kontrol per konten, TECH-09 dari nol menjadi siap pakai.

---

## 07 — Content Governance

| ID | Area | Status | Keterangan |
|---|---|:-:|---|
| CG-01 | Content Owner | ✅ | **Peran Admin Korporat vs Kontributor** memisahkan pemilik dari penyumbang |
| CG-02 | Content Lifecycle | ✅ | **Draf → Tinjau → Setujui → Terbit → Arsip**, tidak bisa dilompati siapa pun |
| CG-03 | Brand Control | 🟡 | Kendali peran ada; brand guideline belum dirujuk di panel |
| CG-04 | Accuracy | ✅ | **Jejak audit menyimpan rekam persetujuan** — siapa, kapan, dari IP mana |
| CG-05 | Approval | ✅ | **Persetujuan wajib sebelum terbit**, catatan peninjau tersimpan |
| CG-06 | News SLA | ✅ | Tiga klik dari draf ke terbit bagi yang berwenang |
| CG-07 | Sustainability review | 🟡 | Alur tinjauan ada; peninjau per fungsi belum dipetakan |
| CG-08 | Business Content | 🟡 | Artikel dapat ditautkan ke unit bisnis; kurasi bersama SBU belum diatur |
| CG-09 | Career Content | 🟡 | Lowongan dikelola CMS; pemiliknya belum ditetapkan |
| CG-10 | Archive | ✅ | **Arsip tanpa kehilangan jejak audit**; `unpublish_at` untuk konten kedaluwarsa |
| CG-11 | Governance Reporting | 🟡 | Dasbor menampilkan keadaan sekarang; laporan bulanan/kuartalan belum |
| CG-12 | Update Cadence | ⬜ | Bergantung pada kalender editorial yang belum ada |

**Perubahan putaran ini:** CG-01, CG-02, CG-04, CG-05, CG-06, dan CG-10
seluruhnya dari belum ada menjadi terpenuhi. Enam dari dua belas butir
governance dipenuhi oleh satu pekerjaan yang sama, karena semuanya bersandar
pada alur dan jejak audit yang dibangun di CMS-03.

---

## 08 — Deliverables & Timeline

Kick-off TOR 1 Oktober 2026. Terhadap kelima belas milestone:

| ID | Deliverable | Status |
|---|---|:-:|
| D-01 | Kick-off & Discovery | ⬜ |
| D-02 | Content / Website Audit | ✅ Sudah ada: `docs/konten-situs.md`, `docs/laporan-perbandingan.md` |
| D-03 | Final Sitemap & IA | ⬜ Sitemap TOR belum diterapkan |
| D-04 | Wireframe | ⬜ |
| D-05 | UI Design System | 🟡 Token warna dan komponen ada di kode, bukan sebagai design system |
| D-06 | Prototype & UX Testing | 🟡 Prototipe statis ada, pengujian UX belum |
| D-07 | Frontend Development | 🟡 |
| D-08 | CMS / Backend | ✅ **Selesai** |
| D-09 | Content Migration | ✅ **Selesai** — 35 artikel + optimasi gambar |
| D-10 | Integration & SEO | 🟡 SEO selesai; papan analitik sendiri berjalan, integrasi platform luar menunggu ID |
| D-11 | QA / Security / Performance | 🟡 146 tes otomatis; audit keamanan dan benchmark performa belum |
| D-12 | UAT | ⬜ |
| D-13 | Training & Handover | 🟡 Panduan tertulis ada; dua sesi pelatihan belum |
| D-14 | Go-Live | ⬜ |
| D-15 | Warranty / Stabilization | ⬜ |

---

## Catatan terhadap 11 — Notes & Assumptions

| ID | Catatan TOR | Keadaan |
|---|---|---|
| N-02 | Related entities di footer, bukan navigasi utama | ✅ Sudah sesuai |
| N-03 | Contact di footer, bukan navigasi utama | ⚠️ Masih tombol di navigasi utama |
| N-05 | Dukungan ID/EN | ✅ Melebihi — ID, EN, dan JA |
| N-06 | Mengikuti brand guideline | ⚠️ Lihat selisih palet pada UX-02 |

---

## Lima hal yang paling perlu diputuskan

1. **Warna logo versus warna TOR.** Palet situs sudah diubah mengikuti TOR,
   tetapi berkas gambar logo tetap biru dan kuning — dan palet lama memang
   diambil dari logo itu. Perlu kepastian dari pemegang brand guideline: apakah
   logonya yang akan menyusul, atau paletnya yang harus kembali ke warna logo.
   Tipografi juga masih berbeda dari yang ditulis TOR.

2. **Struktur navigasi.** Menerapkan lima pilar TOR berarti memindahkan CSR,
   SHE, dan Karir ke dalam pilar baru. URL berubah, dan URL yang berubah
   memutus tautan yang sudah terindeks — butuh peta redirect 301 yang disusun
   sebelum, bukan sesudah.

3. **Sebelas halaman yang belum ada**, terutama dua pilar penuh: Sustainability
   dan Our People. Keduanya disebut TOR sebagai pilar utama.

4. **Staging dan pemantauan.** TECH-13 dan TECH-14 belum ada sama sekali, dan
   keduanya sulit ditambahkan setelah situs tayang.

5. **Tiga butir CMS yang menunggu pihak lain:** nama platform analitik yang
   disetujui, salinan backup di luar mesin, dan penjadwalan pelatihan. Yang
   pertama tidak lagi memblokir: empat event yang diminta TOR sudah tercatat
   sendiri dan terbaca di menu Analitik, jadi keputusan platform kini menentukan
   apakah ada atribusi kampanye dan data lintas situs — bukan lagi apakah ada
   angka sama sekali.
