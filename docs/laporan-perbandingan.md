# Laporan Perbandingan: Website Lama vs. Prototype Rejuvenation
### Hasnur Group, hasnurgroup.com

**Dibuat:** 22 September 2026
**Dibandingkan:**
- **Website Lama (Live):** https://www.hasnurgroup.com/
- **Prototype Rejuvenation:** folder `PROTOTYPE/` (21 halaman statis: `index.html` + 20 halaman `/page/*`)

**Metodologi:** Semua angka di laporan ini diambil langsung dari HTML mentah kedua situs (bukan estimasi). Website lama di-fetch via `curl`, prototype dibaca langsung dari filesystem. Sumber konten: `docs/spesifikasi.md` dan `docs/konten-situs.md`.

---

## 1. Ringkasan Eksekutif

| Aspek | Website Lama | Prototype Rejuvenation |
|---|---|---|
| Tech stack | Custom PHP CMS + jQuery 2.1.4 + Bootstrap 3-era vendor libraries | Tailwind CSS + Alpine.js (2 dependency, keduanya via CDN) |
| Jumlah request eksternal (homepage) | 24 `<script>` + 19 `<link rel="stylesheet">` = **43 request** | 3 `<script>` + 3 `<link>` = **6 request** |
| Berat gambar homepage | ±16 MB (20 file, tanpa lazy-load) | ±9.7 MB (13 file, dengan lazy-load) |
| Lazy loading gambar | Tidak ada (`loading="lazy"` = 0 kemunculan) | Ada di seluruh 21 halaman |
| Link LinkedIn di footer | **Rusak** (`hDps://`, typo protokol) | Diperbaiki (`https://`) |
| Timeline sejarah | Widget `cd-horizontal-timeline` (jQuery, klik-per-event) | Semua 15 titik tampil sekaligus, animasi scroll-reveal otomatis |
| Warna dominan | Belum konsisten dieksplisitkan dari brand asset | Diambil presisi dari pixel `logo.png` (biru `#154F91`, kuning `#FFE80A`, hijau `#2A9941`, merah `#E03633`) |
| Cakupan halaman | 20 halaman + puluhan artikel individual | 20 halaman (menyamai seluruh item navbar) |

**Kesimpulan singkat:** Prototype secara struktur konten sudah menyamai 100% sitemap navbar website lama (20/20 halaman), dengan tech stack jauh lebih ringan dan UI yang dimodernisasi, sesuai arahan spec. **tanpa mengubah isi/konten**.

---

## 2. Tech Stack

### 2.1 Website Lama
Diidentifikasi dari `<script>`/`<link>` tag di HTML:
- **Frontend:** jQuery **2.1.4** (dirilis 2014, sudah sangat lawas, versi stabil terbaru jQuery saat ini adalah 3.7.x)
- **CSS framework:** Bootstrap (versi lama, tidak terdeteksi versi pastinya dari nama file)
- **Vendor plugin:** owl-carousel, fancyBox, bootstrap-datepicker, bootstrap-select, bootstrap-slider, jquery.rateyo (star-rating, tidak terlihat dipakai di homepage), jquery.stellar (parallax), jquery.counterup, Font Awesome (2 versi berbeda dimuat sekaligus, versi lokal dan versi cdnjs), Material Design Icons, icomoon, Splide (untuk sebagian carousel)
- **Backend:** CMS custom (routing `/page/...`, `/post/...`, kemungkinan Laravel/CodeIgniter versi lama berdasarkan pola URL, tidak bisa dipastikan tanpa akses source)
- **Total dependency di homepage:** 24 script + 19 stylesheet = 43 file eksternal yang harus di-*fetch* sebelum halaman fully interactive

### 2.2 Prototype Rejuvenation
- **Frontend:** Tailwind CSS (Play CDN, akan diganti build pipeline saat masuk fase Laravel) + Alpine.js 3.x untuk interaktivitas ringan (dropdown nav, mobile menu, hero slider, gallery)
- **Backend:** belum ada (fase ini murni Frontend statis sesuai scope "Phase 1"), sesuai roadmap di `docs/spesifikasi.md` §7. Laravel + Blade + MySQL menyusul di fase berikut
- **Total dependency di homepage:** 3 script (Tailwind, Alpine, Google Fonts) + 3 link = 6 file eksternal

**Dampak:** pengurangan ±86% jumlah request eksternal pada level markup (belum menghitung request internal jQuery-plugin lain yang dimuat lebih lanjut oleh masing-masing vendor script).

---

## 3. Performa & Optimasi Gambar

| Metrik | Website Lama | Prototype |
|---|---|---|
| `loading="lazy"` pada homepage | 0 | 13 (1 `eager` untuk hero, 12 `lazy`) |
| `loading="lazy"` di seluruh situs (21 halaman) | tidak diverifikasi per-halaman (di luar scope fetch) | 44 kemunculan `lazy` + 21 `eager` (1 per halaman, untuk gambar hero yang harus tampil instan) |
| Berat gambar homepage (file yang sama, diukur dari source asli) | ±16 MB / 20 file | ±9.7 MB / 13 file |
| Format gambar | JPEG/PNG saja, tidak ada WebP | JPEG/PNG saja (WebP belum diterapkan, lihat §6 Keterbatasan) |
| Preconnect font | Tidak ada | Ada (`rel="preconnect"` ke Google Fonts) |

Beberapa foto artikel di website lama berukuran sangat besar untuk konten web. Contoh: foto publikasi "Hasnur Berbagi Qurban" **7,2 MB** dan "Pongamia Project" **5,7 MB**, keduanya resolusi asli kamera (5000×3750 px) yang langsung di-serve tanpa kompresi/resize untuk web. Ini adalah salah satu penyebab utama beratnya halaman Publikasi di situs lama.

---

## 4. Desain & Identitas Visual

| Aspek | Website Lama | Prototype |
|---|---|---|
| Palet warna | Tidak eksplisit. Kombinasi warna bervariasi antar section, tidak selalu konsisten dengan logo | Diambil presisi via color-sampling pixel `logo.png`: biru `#154F91`, kuning `#FFE80A`, hijau `#2A9941`, merah `#E03633`, dipakai konsisten sebagai brand stripe, blob dekoratif, dan aksen di seluruh hero |
| Tipografi | Font sistem/legacy (tidak terverifikasi web font custom) | Plus Jakarta Sans (Google Fonts), konsisten di seluruh 21 halaman |
| Hero/banner | Slider gambar statis dalam frame tetap | Full-width, gambar tidak pernah terpotong (`h-auto`, bukan `object-cover` dipaksa), dilengkapi brand-stripe 4 warna logo, floating badge "60 Tahun Berkarya" dengan animasi, dan blob warna brand yang bergerak halus di background |
| Komponen interaktif | jQuery plugin per-fitur (owl-carousel, fancybox modal, dsb) | Alpine.js reaktif: dropdown nav, mobile menu, hero slider (siap multi-slide), scroll-reveal animation di seluruh section |
| Responsive | Bootstrap grid (breakpoint md/lg klasik) | Tailwind utility responsive (`sm:`/`md:`/`lg:`), mobile-first, hamburger menu dengan `<details>` accordion untuk sub-nav di mobile |

---

## 5. Struktur Konten & Navigasi

Sitemap navbar website lama (7 kategori utama, 20 halaman turunan) **seluruhnya direplikasi 1:1** di prototype:

| Kategori | Item | Status Prototype |
|---|---|---|
| Tentang Kami | Ikhtisar, Visi Misi, Nilai Inti | ✅ 3/3 dibangun, konten hasil scrape penuh |
| Bisnis Kami | 7 SBU (Logistik, Agribisnis & Kehutanan, Energi, Teknologi & Jasa, Pendidikan, Consumer, Investasi) | ✅ 7/7 dibangun |
| CSR | Ikhtisar, Aktifitas CSR | ✅ 2/2 dibangun |
| SHE | Ikhtisar, Aktivitas SHE | ✅ 2/2 dibangun |
| Karir | Lowongan Kami | ✅ 1/1 dibangun |
| Publikasi | Update Terbaru, Berita, Press Release, Multimedia, Galeri | ✅ 5/5 dibangun |
| **Total** | | **20/20 halaman (100%)** |

Setiap halaman artikel (Tentang Kami, Bisnis Kami, CSR/SHE Overview, Karir) memakai **struktur asli** dari situs lama. Heading, paragraf, dan bullet list dipetakan otomatis dari elemen HTML aslinya (bukan re-tulis manual), sehingga tidak ada penyimpangan isi. Halaman listing (Berita/Press Release/CSR-SHE Activity) menampilkan kartu lengkap (judul, foto, ringkasan) dari situs asli.

**Perbedaan cakupan yang disengaja:** artikel individual (`/post/...`). Jumlahnya puluhan dan terus bertambah, **tidak dibuatkan halaman detail** di prototype ini, karena tidak termasuk item navbar. Tombol "Baca Selengkapnya" di kartu publikasi mengarah ke artikel aslinya di hasnurgroup.com. Ini akan menjadi bagian dari CMS dinamis (`Post` model) di fase Laravel sesuai roadmap spec.

---

## 6. Isu di Website Lama yang Ditemukan & Diperbaiki

| # | Isu | Bukti | Status di Prototype |
|---|---|---|---|
| 1 | Link LinkedIn footer rusak | `href="hDps://www.linkedin.com/in/hasnur-group-91582a336/"` (typo protokol `hDps` alih-alih `https`, dan mengarah ke URL personal `/in/`, bukan halaman perusahaan) | Diperbaiki ke `https://www.linkedin.com/company/hasnurgroup` |
| 2 | Tidak ada lazy loading gambar | 0 atribut `loading="lazy"` di seluruh homepage | Diterapkan di seluruh 21 halaman |
| 3 | Timeline sejarah butuh klik per-tahun untuk dibaca | Widget `cd-horizontal-timeline` (plugin jQuery 2015-era) | Semua 15 titik tampil sekaligus, muncul dengan animasi scroll. Tidak perlu klik |
| 4 | Ketidakkonsistenan data SBU | Halaman *Tentang Kami → Ikhtisar* masih menyebut **"6 Strategic Business Unit"**, sementara Beranda & struktur navbar sudah memakai **7 SBU** (pasca-restrukturisasi 2023) | *Belum diperbaiki*. Dipertahankan apa adanya sesuai instruksi "jangan ubah konten"; direkomendasikan sebagai catatan untuk tim konten (lihat §8) |
| 5 | Percampuran bahasa (nav Indonesia, sebagian berita full English) | Contoh: judul berita *"HD HYUNDAI CONSTRUCTION EQUIPMENT PARTNERS WITH INDONESIAN MINING FIRM HASNUR GROUP"* tampil apa adanya di tengah UI berbahasa Indonesia | *Belum diperbaiki*. Konten dipertahankan asli (bukan tugas Frontend Phase 1); language switcher diusulkan spec sebagai perbaikan lanjutan |
| 6 | 24+19 = 43 request eksternal per halaman | Dihitung dari tag `<script src>`/`<link rel="stylesheet">` | Diturunkan menjadi 6 request |

---

## 7. Keterbatasan Prototype Saat Ini

Supaya laporan ini jujur dan tidak berat sebelah, berikut hal-hal yang **website lama masih unggul** atau yang **belum** dikerjakan di prototype:

1. **Backend & CMS**. Website lama sudah live dengan data dinamis (Publikasi/Berita/CSR di-update rutin lewat admin panel). Prototype masih 100% statis HTML; fase Laravel + Filament (sesuai spec §2 & §7) belum dimulai.
2. **Halaman artikel individual**. Website lama punya puluhan halaman detail artikel per post; prototype hanya membangun halaman *listing*-nya (lihat §5).
3. **WebP & kompresi gambar**. Spec mengusulkan WebP untuk hero slider & grid SBU; prototype saat ini masih memakai JPEG/PNG asli hasil scrape (belum dikonversi/dikompres).
4. **Search & filter**. Website lama punya beberapa fitur pendukung (misalnya bootstrap-datepicker, bootstrap-select) yang mengindikasikan ada form filter di suatu halaman (kemungkinan di halaman Karir/Lowongan) yang belum tereplikasi karena scope Phase 1 hanya sitemap navbar utama.
5. **Multi-bahasa**, Belum ada language switcher (diusulkan spec, belum diimplementasikan).

---

## 8. Rekomendasi Tindak Lanjut

1. **Konfirmasi ke tim konten**: apakah "6 SBU" di halaman Ikhtisar (isu #4 di §6) memang perlu diupdate ke "7 SBU" agar konsisten dengan Beranda dan struktur navbar, ini murni pertanyaan data, bukan keputusan teknis Frontend.
2. **Lanjut ke Laravel Phase 2** sesuai `docs/spesifikasi.md` §7: migration/model (Post, BusinessUnit, Vacancy, Office, SlideBanner), seed dari data yang sudah di-scrape di prototype ini, lalu admin panel untuk Publikasi/Karir.
3. **Optimasi gambar**: convert aset besar (terutama foto publikasi 5-7 MB) ke WebP + resize sebelum masuk produksi.
4. **Bangun halaman detail artikel** (`/post/...`) sebagai bagian dari `Post` model dinamis di Laravel, menggantikan tautan sementara ke situs lama.

---

## 9. Pembaruan 23 September 2026

Bagian ini mencatat apa yang berubah setelah laporan di atas ditulis. §3 dan §7
sengaja tidak diubah. Keduanya potret keadaan per 22 September.

### 9.1 Artikel tanpa tanggal terbit: dihapus permanen

Crawler menarik **61 artikel** dari situs lama. **26 di antaranya tidak punya
tanggal terbit sama sekali** di sumbernya. Tanggal tidak pernah ditebak, dan
pada 23 September 2026 diputuskan ke-26 artikel itu **dihapus permanen**, bukan
ditunda menunggu konfirmasi tanggal.

Konsekuensi yang ikut ditangani:

| Hal | Penanganan |
|---|---|
| 26 URL `/post/...` sudah terindeks Google | Dijawab **410 Gone**, bukan 404. 410 berarti "memang sudah tidak ada" dan dikeluarkan dari indeks lebih cepat; 404 dianggap mungkin sementara. |
| Pengunjung dari hasil pencarian | Mendapat halaman penjelasan berbahasa Indonesia dengan tautan ke kategori terkait dan beranda, bukan halaman error kerangka kerja. |
| Impor ulang menghidupkannya lagi | `php artisan import:articles` kini **melewati** artikel tanpa tanggal. Berkas crawl-nya tetap disimpan; `--with-undated` membatalkan keputusan ini bila suatu saat diperlukan. |
| Kategori `she-activity` | Seluruh 8 artikelnya termasuk yang dihapus, jadi daftarnya kosong. Halamannya tetap bisa dibuka dengan keterangan "Belum ada publikasi", tapi **tidak diajukan ke sitemap**. Halaman kosong berisiko dinilai soft 404 oleh Google. Masuk lagi otomatis begitu ada isinya. |

Sisa isi: **35 artikel internal + 1 kutipan liputan media**. Kutipan itu hanya
judul, media, tanggal, dan tautan keluar. Isi beritanya milik penerbitnya dan
tidak disalin ke domain Hasnur.

### 9.2 Yang sudah tidak berlaku lagi dari §7

| §7 | Keadaan 23 September 2026 |
|---|---|
| "Backend & CMS belum dimulai" | Laravel 13 berjalan: 5 model, 21 halaman jadi Blade, konten artikel dari database. Filament **belum**. Butuh koneksi internet yang belum tersedia di mesin kerja. |
| "Halaman artikel individual belum ada" | Sudah ada di `/post/{kategori}/{slug}`, persis bentuk URL situs lama, jadi tidak perlu redirect untuk yang masih hidup. |
| "WebP & kompresi gambar belum" | Sudah: varian WebP 400/800/1600 px dengan gambar asli tetap sebagai fallback. Berat gambar 18 halaman turun **131,8 MB → 4,4 MB (97%)** pada desktop 1440 px. Berkas variannya sendiri belum masuk repo. Keputusan 45 MB masih terbuka. |

### 9.3 Yang masih terbuka

1. **Filament dan build Tailwind** menunggu mesin dengan koneksi internet.
   Tailwind masih memakai Play CDN, yang membangun CSS di browser dan merugikan LCP.
2. **45 MB varian WebP** belum diputuskan masuk repo atau dibuat saat deploy.
3. **8 dateline `she-activity`**. Bila suatu saat dikonfirmasi, kategorinya bisa
   hidup kembali dengan satu perintah impor.
4. **Gambar galeri hanya 550×309 px** tapi dipasang full-bleed 28:9. Ini butuh
   foto sumber yang lebih baik, bukan konversi.

Pemeriksaan sebelum cutover dapat diulang kapan saja dengan
`python3 tools/qa_check.py`. Hasil terakhir: 55 URL sitemap semuanya 200, 61
tautan internal tidak ada yang mati, 61 URL artikel situs lama terjawab (35
hidup, 26 dijawab 410), tanpa temuan.

---

## Lampiran: Sumber Data

- HTML mentah website lama: fetched langsung dari `https://www.hasnurgroup.com/` dan 20 URL `/page/*` pada 22 September 2026
- 118 gambar asli di-download dari sumbernya dan disimpan di `assets/img/` prototype
- Struktur konten diverifikasi otomatis: 134 referensi gambar & 1038 link internal di seluruh 21 halaman prototype. **0 broken**
