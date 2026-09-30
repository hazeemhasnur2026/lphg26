# Audit Kepatuhan, Keamanan, dan Kekayaan Intelektual

**Objek audit:** repositori `halhzm9/hasnurgroup-landing-rejuvenation`, cabang `main`,
commit `71fa82f`
**Tanggal:** 24 September 2026
**Metode:** pemeriksaan pasif atas source code, konfigurasi, dependensi, riwayat git,
dan aplikasi yang dijalankan lokal. Tidak ada eksploitasi, tidak ada pengujian
destruktif, tidak ada akses ke sistem pihak ketiga.

> Dokumen ini adalah penilaian kepatuhan teknis, bukan opini hukum dari advokat atau
> konsultan hukum. Kutipan pasal di bawah menyebut nomor pasal sebagaimana dirujuk
> dalam permintaan audit; bunyi lengkapnya perlu diverifikasi ke JDIH sebelum dipakai
> sebagai dasar keputusan.

---

## Batas audit yang harus dibaca lebih dulu

**URL produksi tidak diberikan, dan aplikasi ini belum pernah di-deploy.** Seluruh
temuan di bawah berasal dari source code dan aplikasi yang berjalan di mesin
pengembang. Hal yang hanya bisa dinilai dari server hidup, yaitu sertifikat TLS,
header yang benar-benar dikirim web server, konfigurasi firewall, port terbuka,
backup yang benar-benar berjalan, dan log akses, semuanya ditandai `UNKNOWN`.

**Repositori bersifat privat.** Diverifikasi lewat `gh api` (`private: true`) dan
permintaan anonim ke API GitHub yang menjawab 404. Catatan proyek sebelumnya
menyatakan repositori ini publik dan itu keliru; beberapa pesan commit sebelum
`71fa82f` masih memuat klaim yang salah itu.

---

## 1. Ringkasan Eksekutif

1. **Tidak ada Kebijakan Privasi, Syarat dan Ketentuan, maupun Kebijakan Cookie** di
   seluruh situs, padahal formulir Hubungi Kami mengumpulkan nama, surel, nomor
   telepon, isi pesan, dan alamat IP. Ini celah kepatuhan paling besar terhadap
   UU 27/2022.
2. **Tidak ada mekanisme persetujuan** pada formulir, dan tidak ada pernyataan dasar
   pemrosesan data.
3. **Tidak ada kebijakan retensi, pemangkasan otomatis, maupun backup** di kode.
   Pesan masuk tersimpan tanpa batas waktu.
4. **Template `.env` yang disalin ke server berisi `APP_ENV=local`, `APP_DEBUG=true`,
   dan `LOG_LEVEL=debug`.** Jika terpakai apa adanya di produksi, halaman error akan
   menampilkan jejak tumpukan beserta isi variabel lingkungan kepada pengunjung.
5. **Tidak ada satu pun header keamanan HTTP** yang dikirim aplikasi, dan
   `session.secure` bernilai null sehingga cookie sesi tidak dipaksa lewat HTTPS.
6. **Setiap baris di tabel `users` adalah pengelola penuh.** Tidak ada sistem peran,
   dan `User::canAccessPanel()` mengembalikan `true` tanpa syarat.
7. **Komponen unggahan menerima `image/*`, yang mencakup SVG**, dan tidak ada batas
   ukuran berkas. SVG yang disajikan dari domain yang sama berpotensi menjadi jalur
   XSS tersimpan.
8. **Tidak ditemukan kredensial di riwayat git** dan tidak ada berkas `.env`, kunci
   privat, atau sertifikat yang terlacak. Ini bagian yang sudah sehat.
9. **Lisensi dependensi bersih**: 110 MIT, 30 BSD-3-Clause, dan tidak ada satu pun
   paket berlisensi tidak dikenal. Tiga paket `nette/*` berlisensi rangkap dan dapat
   dipakai di bawah BSD-3-Clause, bukan kewajiban GPL.
10. **Kepemilikan kekayaan intelektual tidak dapat ditentukan.** Tidak ada berkas
    LICENSE, tidak ada perjanjian magang, NDA, maupun IP assignment di repositori,
    padahal pekerjaan ini dikerjakan oleh peserta magang dengan bantuan AI.

---

## 2. Peta Arsitektur

```
Pengunjung
   |
   v
Laravel 13 (backend/public)               <- belum di-deploy, UNKNOWN di produksi
   |-- rute bahasa: / (id), /en, /ja
   |-- halaman publik: Blade + Tailwind + Alpine, seluruh aset dari domain sendiri
   |-- formulir Hubungi Kami (POST, throttle 5/10 menit, honeypot)
   |
   v
Panel admin Filament 4.13 (/admin)        <- autentikasi sesi, tanpa peran
   |
   v
Database: SQLite (dev) atau MySQL         <- pilihan lewat .env
   |-- posts, business_units, slide_banners, offices, vacancies
   |-- contact_messages  (DATA PRIBADI)
   |-- users             (DATA PRIBADI)
   |-- sessions          (DATA PRIBADI: ip_address, user_agent)
   |
   v
Surel keluar (SMTP)                       <- satu penerima: MAIL_CONTACT_RECIPIENT

Pihak ketiga yang menerima data pengunjung: TIDAK ADA
```

**Diverifikasi:** halaman publik tidak memuat satu pun aset dari domain pihak ketiga.
Tidak ada Google Analytics, Tag Manager, Meta Pixel, Hotjar, Sentry, peta, maupun
chatbot. Huruf Plus Jakarta Sans diunduh saat build dan dilayani dari domain sendiri.
Satu-satunya URL eksternal di halaman adalah tautan keluar ke media sosial Hasnur,
dua situs afiliasi, dan satu kutipan berita ANTARA.

---

## 3. Inventaris Teknologi

| Teknologi | Bukti | Keyakinan | Fungsi | Risiko |
|---|---|---|---|---|
| Laravel 13.33 | `composer.lock` | Tinggi | kerangka aplikasi | perlu PHP 8.4+ |
| Filament 4.13.4 | `composer.lock`, `app/Filament` | Tinggi | panel admin | akses tanpa peran |
| Livewire 3 | dependensi Filament | Tinggi | komponen dinamis admin | - |
| Alpine.js 3 | `resources/js/app.js` | Tinggi | interaksi halaman publik | - |
| Tailwind CSS 4 + Vite 8 | `package.json` | Tinggi | membangun CSS dan JS | build wajib saat deploy |
| SQLite / MySQL | `config/database.php`, `.env` | Tinggi | penyimpanan | SQLite butuh berkas di luar document root |
| mcamara/laravel-localization | `composer.lock` | Tinggi | rute tiga bahasa | - |
| spatie/laravel-translatable | `composer.lock` | Tinggi | terpasang, belum dipakai | dependensi menganggur |
| GitHub Actions | `.github/workflows` | Tinggi | CI dan deploy | rahasia deploy belum diisi |

---

## 4. Inventaris Data dan Klasifikasi

### Data Pribadi (UU 27/2022 Pasal 4 ayat 1)

| Tabel | Kolom | Sumber | Dasar pemrosesan | Status |
|---|---|---|---|---|
| `contact_messages` | `name`, `email`, `phone`, `message`, `ip_address` | pengunjung | belum dinyatakan | **celah** |
| `users` | `name`, `email`, `password` (hash) | pengelola | hubungan kerja | perlu didokumentasikan |
| `sessions` | `ip_address`, `user_agent`, `payload` | otomatis Laravel | operasional | perlu retensi |

### Data Pribadi Spesifik (Pasal 4 ayat 2)

**Tidak ditemukan.** Tidak ada data kesehatan, biometrik, genetika, catatan kejahatan,
data anak, maupun data keuangan pribadi. Medan pesan bebas pada formulir secara teknis
memungkinkan pengunjung menuliskannya sendiri; ini risiko yang melekat pada setiap
formulir bebas dan ditangani lewat kebijakan, bukan kode.

### Data Publik

Isi artikel, profil unit bisnis, alamat kantor, dan lowongan. Berasal dari situs lama
milik Hasnur Group sendiri.

---

## 5. Matriks Kepatuhan UU PDP (UU No. 27 Tahun 2022)

| Pasal | Kewajiban | Kondisi saat ini | Status |
|---|---|---|---|
| 4 | Klasifikasi data pribadi | Data umum diproses; data spesifik tidak | Terpenuhi |
| 5-15 | Hak subjek data: akses, koreksi, penghapusan, penarikan persetujuan | Tidak ada mekanisme apa pun, tidak ada kanal permintaan | **Tidak terpenuhi** |
| 16 | Pemrosesan terbatas, spesifik, sah, transparan | Pengumpulan terbatas dan proporsional, tetapi tidak transparan karena tidak ada pemberitahuan | **Sebagian** |
| 20 | Dasar pemrosesan dinyatakan | Tidak dinyatakan di mana pun | **Tidak terpenuhi** |
| 35 | Keamanan data pribadi | Kata sandi ter-hash, CSRF aktif, pembatasan laju ada; header keamanan tidak ada, cookie tidak dipaksa HTTPS, tanpa peran | **Sebagian** |
| 39 | Pencegahan akses tidak sah | Autentikasi sesi ada; seluruh akun berwenang penuh | **Sebagian** |
| 46 | Pemberitahuan kebocoran dalam 3x24 jam | Tidak ada deteksi, prosedur, maupun penanggung jawab | **Tidak terpenuhi** |
| 47 | Akuntabilitas pengendali | Tidak ada dokumentasi pemrosesan | **Tidak terpenuhi** |
| 57 | Sanksi administratif | Paparan meningkat selama Pasal 5-15, 20, dan 46 belum dipenuhi | Risiko |
| 65-67 | Larangan pidana | Tidak ditemukan indikasi pengambilan atau pengungkapan melawan hukum | Tidak ada indikasi |

**Catatan penting soal Pasal 20.** Persetujuan bukan satu-satunya dasar yang tersedia.
Untuk formulir kontak yang dikirim atas inisiatif pengunjung sendiri, dasar
"kepentingan sah pengendali" atau "langkah sebelum perjanjian" sering lebih tepat
daripada meminta centang persetujuan. Pilihan dasar ini keputusan legal, bukan teknis.

---

## 6. Matriks Hak Cipta dan Aset

| Aset | Sumber | Pemegang hak | Bukti | Status |
|---|---|---|---|---|
| Isi 35 artikel | crawl situs lama Hasnur | PT Hasnur Group | `data/discovered-urls.json`, `source_url` tiap baris | Hijau |
| 1 kutipan liputan media | ANTARA | penerbit | `external_url` diisi, `body` sengaja kosong | Hijau |
| 139 MB foto artikel | crawl situs lama Hasnur | PT Hasnur Group | `content-migration/` | Hijau |
| Logo, key visual, panduan merek | perusahaan | PT Hasnur Group | `assets/brand/` | Hijau |
| Foto galeri dan banner halaman | situs lama | PT Hasnur Group | `assets/img/` | Hijau |
| Huruf Plus Jakarta Sans | Google Fonts / Bunny | Tokotype | dibundel saat build | **Kuning** |
| Ikon SVG di markup | ditulis tangan mengikuti pola Heroicons | perlu dipastikan | tidak ada catatan asal | **Kuning** |
| Kode sumber aplikasi | pekerjaan magang berbantuan AI | **tidak jelas** | tidak ada LICENSE, tidak ada IP assignment | **Merah** |

**Yang sudah ditangani dengan benar:** permintaan sebelumnya untuk menyalin isi berita
dari media lain ke dalam kategori `news` tidak dijalankan. ANTARA mencantumkan
pernyataan hak cipta dan larangan pengambilan otomatis tanpa izin tertulis. Yang
dibangun sebagai gantinya adalah pencatatan rujukan: judul, media, tanggal, dan tautan
keluar, dengan badan artikel dibiarkan kosong. Judul, nama media, dan tanggal adalah
fakta; teks beritanya tidak diambil.

**Plus Jakarta Sans** dirilis Tokotype di bawah SIL Open Font License 1.1, yang
mengizinkan penggunaan komersial dan penyertaan dalam produk. Kewajibannya adalah
menyertakan berkas lisensi. Saat ini berkas OFL tidak ikut disertakan dalam repositori.

---

## 7. Matriks Rahasia Dagang (UU No. 30 Tahun 2000)

Informasi yang berpotensi memenuhi kriteria rahasia dagang (tidak diketahui umum,
bernilai ekonomi, dijaga kerahasiaannya):

| Informasi | Lokasi | Kriteria terpenuhi? | Kontrol saat ini |
|---|---|---|---|
| Panduan merek internal | `assets/brand/*.pdf` (940 KB) | Bernilai, tidak umum | Repositori privat |
| Arsitektur dan konfigurasi infrastruktur | `README.md`, `deploy/` | Bernilai terbatas | Repositori privat |
| Kredensial deploy | GitHub Secrets | Ya | Tidak pernah masuk repositori |
| Basis data pesan masuk | tabel `contact_messages` | Ya | Akses admin tanpa peran |

**Kontrol yang belum ada:** tidak ada NDA atau perjanjian magang di repositori, tidak
ada klasifikasi dokumen, tidak ada pencatatan akses, dan tidak ada prosedur pencabutan
akses saat masa magang berakhir. Kriteria ketiga UU 30/2000, yaitu "dijaga
kerahasiaannya", bergantung pada adanya upaya nyata menjaga. Repositori privat adalah
satu bentuk upaya itu, tetapi tanpa NDA dan prosedur offboarding, buktinya tipis.

---

## 8. Matriks Lisensi Open Source

| Lisensi | Jumlah paket PHP | Jumlah paket Node | Kewajiban |
|---|---|---|---|
| MIT | 110 | 70 | sertakan teks lisensi dan pernyataan hak cipta |
| BSD-3-Clause | 30 | 1 | sertakan teks lisensi, jangan pakai nama kontributor untuk promosi |
| MPL-2.0 | 0 | 24 | copyleft per berkas; wajib membuka sumber hanya jika berkasnya dimodifikasi |
| ISC | 0 | 6 | setara MIT |
| Apache-2.0 | 1 | 2 | sertakan NOTICE bila ada, hibah paten |
| 0BSD | 0 | 1 | tanpa kewajiban |
| BSD-3 atau GPL-2 atau GPL-3 | 3 (`nette/*`) | 0 | **lisensi rangkap, penerima memilih**; pilih BSD-3-Clause |

**Tidak ada paket berlisensi tidak dikenal, dan tidak ada AGPL.** Ketiga paket
`nette/*` tampak mengkhawatirkan pada pemindaian otomatis karena mencantumkan GPL,
tetapi lisensi rangkap berarti penerima boleh memilih salah satunya. Dengan memilih
BSD-3-Clause tidak ada kewajiban membuka sumber.

**Yang belum ada:** berkas atribusi pihak ketiga. MIT dan BSD sama-sama mewajibkan
penyertaan teks lisensi saat perangkat lunak didistribusikan. Selama aplikasi hanya
dijalankan sebagai layanan web dan tidak dibagikan sebagai berkas, kewajiban ini
umumnya tidak terpicu, tetapi menyiapkan `THIRD-PARTY-NOTICES.md` adalah biaya rendah.

---

## 9. Matriks Konfigurasi Keamanan

### Header HTTP

| Header | Status | Dampak |
|---|---|---|
| `Strict-Transport-Security` | **tidak ada** | pengunjung bisa diarahkan ke HTTP |
| `Content-Security-Policy` | **tidak ada** | tidak ada lapisan kedua terhadap XSS |
| `X-Frame-Options` | **tidak ada** | halaman bisa dibingkai situs lain |
| `X-Content-Type-Options` | **tidak ada** | peramban boleh menebak tipe konten |
| `Referrer-Policy` | **tidak ada** | URL internal bocor ke situs tujuan |
| `Permissions-Policy` | **tidak ada** | fitur peramban tidak dibatasi |

### Cookie dan sesi

| Setelan | Nilai | Catatan |
|---|---|---|
| `session.http_only` | `true` | baik |
| `session.same_site` | `lax` | memadai |
| `session.secure` | `null` | **tidak dipaksa HTTPS** |
| `session.encrypt` | `false` | isi sesi tersimpan apa adanya di database |
| `session.lifetime` | 120 menit | wajar |

### Autentikasi dan otorisasi

| Aspek | Status |
|---|---|
| Kata sandi | ter-hash lewat cast `hashed` bawaan Laravel |
| Pembatasan percobaan login | ada, bawaan Filament |
| MFA | **tidak diaktifkan** |
| Pemisahan peran | **tidak ada**, setiap akun berwenang penuh |
| Kebijakan kata sandi | **tidak ada** aturan panjang atau kompleksitas khusus |

### Aplikasi

| Aspek | Status |
|---|---|
| CSRF | aktif, bawaan Laravel, formulir memakai `@csrf` |
| Validasi masukan | `ContactMessageRequest` dengan aturan eksplisit |
| Perangkap robot | ada, medan tersembunyi dengan aturan `prohibited` |
| Pembatasan laju formulir | 5 kiriman per 10 menit |
| Unggahan berkas | `image/*`, **termasuk SVG**, **tanpa batas ukuran** |
| Penanganan galat | `APP_DEBUG=true` di template `.env` |
| Rahasia di kode | **tidak ditemukan** pada seluruh riwayat git |

---

## 10. Analisis Selisih Kebijakan Privasi

```
Yang benar-benar dikumpulkan:
  nama, surel, nomor telepon, perihal, isi pesan, bahasa, alamat IP
  disimpan permanen di contact_messages
  diteruskan sebagai surel ke satu alamat internal

Yang diberitahukan ke pengunjung:
  tidak ada
```

**Status: SELISIH TOTAL.** Tidak ada dokumen kebijakan untuk dibandingkan. Halaman
formulir hanya menyebut bahwa pesan diteruskan ke kotak masuk resmi dan dijawab lewat
surel. Pengumpulan alamat IP tidak disebutkan sama sekali.

---

## 11. Retensi Data

```
Pengumpulan -> Pemrosesan -> Penyimpanan -> Backup -> Retensi -> Penghapusan
    ada           ada          permanen     UNKNOWN   tidak ada   manual saja
```

| Pertanyaan | Jawaban |
|---|---|
| Berapa lama pesan disimpan? | tanpa batas, tidak ada pemangkasan |
| Apakah backup ikut terhapus? | UNKNOWN, backup belum ada |
| Bisakah pengguna meminta penghapusan? | tidak ada kanal |
| Apakah log memuat data pribadi? | tidak, hanya id pesan dan galat |
| Apakah analitik menyimpan pengenal? | tidak ada analitik |

Tabel `sessions` menyimpan alamat IP dan user agent tanpa pemangkasan otomatis.

---

## 12. Kepemilikan dan Kekayaan Intelektual

| Aset | Pembuat | Pemegang saat ini | Bukti | Risiko |
|---|---|---|---|---|
| Kode sumber | peserta magang, berbantuan AI | **tidak jelas** | riwayat git atas nama akun pribadi | **Tinggi** |
| Desain UI | peserta magang mengikuti panduan merek | tidak jelas | tidak ada berkas desain | Sedang |
| Logo dan key visual | perusahaan | PT Hasnur Group | berkas panduan merek | Rendah |
| Isi artikel | perusahaan | PT Hasnur Group | situs lama | Rendah |
| Basis data | dihasilkan dari isi perusahaan | PT Hasnur Group | importer | Rendah |
| Dokumentasi | peserta magang | tidak jelas | repositori | Sedang |

**OWNERSHIP UNCLEAR.** Tidak ditemukan perjanjian magang, NDA, IP assignment, maupun
berkas LICENSE di repositori. Repositori berada di akun pribadi `halhzm9`, bukan akun
organisasi perusahaan. Tanpa penyerahan hak tertulis, kepemilikan hak cipta atas kode
yang ditulis peserta magang tidak otomatis berpindah ke perusahaan.

**Pengembangan berbantuan AI.** Sebagian besar kode dan dokumentasi dalam repositori
ini ditulis dengan bantuan asisten AI, dan pesan commit mencantumkan
`Co-Authored-By: Claude`. Status hak cipta atas keluaran AI berbeda antar yurisdiksi
dan perlu konfirmasi legal, terutama bila perusahaan hendak mendaftarkan hak cipta
atas perangkat lunak ini.

---

## 13. Matriks Risiko

| # | Temuan | Kategori | Bukti | Rujukan | Tingkat | Mitigasi |
|---|---|---|---|---|---|---|
| 1 | Tidak ada Kebijakan Privasi dan pemberitahuan pemrosesan | PDP | tidak ada berkas kebijakan di seluruh repo | UU 27/2022 Ps. 16, 20 | Kritis | Susun kebijakan privasi, tautkan di footer dan formulir |
| 2 | Tidak ada mekanisme hak subjek data | PDP | tidak ada kanal permintaan | UU 27/2022 Ps. 5-15 | Kritis | Sediakan alamat khusus dan prosedur internal |
| 3 | `.env.example` membawa `APP_DEBUG=true` | Keamanan | `backend/.env.example:4` | UU 27/2022 Ps. 35 | Kritis | Ubah template ke `production` dan `false` |
| 4 | Tidak ada prosedur penanganan kebocoran | PDP | tidak ada dokumen | UU 27/2022 Ps. 46 | Tinggi | Susun prosedur dan tunjuk penanggung jawab |
| 5 | Tidak ada header keamanan HTTP | Keamanan | respons aplikasi | UU 27/2022 Ps. 35 | Tinggi | Tambah middleware header dan HSTS di web server |
| 6 | Cookie sesi tidak dipaksa HTTPS | Keamanan | `session.secure = null` | UU 27/2022 Ps. 35 | Tinggi | `SESSION_SECURE_COOKIE=true` di produksi |
| 7 | Semua akun admin berwenang penuh | Keamanan | `User::canAccessPanel()` | UU 27/2022 Ps. 39 | Tinggi | Tambah peran, pisahkan akses pesan masuk |
| 8 | Unggahan menerima SVG tanpa batas ukuran | Keamanan | `FileUpload->image()` | - | Tinggi | Batasi tipe ke jpg/png/webp, pasang `maxSize()` |
| 9 | Kepemilikan kode tidak jelas | IP | tidak ada LICENSE atau IP assignment | UU 28/2014 | Tinggi | Buat IP assignment, pindahkan repo ke akun organisasi |
| 10 | Tidak ada retensi dan penghapusan | PDP | tidak ada pemangkasan | UU 27/2022 Ps. 16 | Sedang | Tetapkan masa simpan, jadwalkan pemangkasan |
| 11 | Tidak ada backup | Operasional | tidak ada di kode maupun dokumen | UU 27/2022 Ps. 35 | Sedang | Backup harian basis data dan folder unggahan |
| 12 | Tidak ada NDA dan prosedur offboarding | Rahasia Dagang | tidak ada dokumen | UU 30/2000 Ps. 3 | Sedang | Lengkapi NDA, catat pencabutan akses |
| 13 | Lisensi huruf tidak disertakan | Hak Cipta | tidak ada berkas OFL | SIL OFL 1.1 | Rendah | Sertakan berkas lisensi huruf |
| 14 | Tidak ada atribusi pihak ketiga | Lisensi | tidak ada berkas notices | MIT, BSD | Rendah | Buat `THIRD-PARTY-NOTICES.md` |
| 15 | Tidak ada MFA untuk admin | Keamanan | konfigurasi panel | UU 27/2022 Ps. 35 | Rendah | Aktifkan MFA bawaan Filament |

---

## 14. Peta Jalan Mitigasi

### P0, sebelum situs dapat diakses publik

1. Ubah `.env.example` ke `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`,
   dan `SESSION_SECURE_COOKIE=true`.
2. Terbitkan Kebijakan Privasi dan tautkan dari footer serta halaman formulir.
   Sebutkan jenis data, tujuan, dasar pemrosesan, masa simpan, dan kanal hak subjek.
3. Tambahkan header keamanan HTTP lewat middleware dan HSTS di web server.
4. Batasi unggahan ke `image/jpeg`, `image/png`, `image/webp` dan pasang batas ukuran.

### P1, dua sampai empat minggu

5. Tambahkan peran pengguna. Pisahkan akses ke `contact_messages` dari akses ke konten.
6. Tetapkan masa simpan pesan masuk, jadwalkan pemangkasan otomatis dan pemangkasan
   `sessions`.
7. Susun prosedur penanganan kebocoran dengan tenggat 3x24 jam dan penanggung jawab.
8. Jalankan backup harian basis data dan folder unggahan, lalu uji pemulihannya.
9. Selesaikan IP assignment dan NDA, pindahkan repositori ke akun organisasi.

### P2, satu sampai tiga bulan

10. Aktifkan MFA untuk seluruh akun admin.
11. Susun catatan kegiatan pemrosesan data sebagai bukti akuntabilitas Pasal 47.
12. Sertakan berkas lisensi huruf dan `THIRD-PARTY-NOTICES.md`.
13. Pasang pemantauan ketersediaan dan pencatatan galat.

### P3, berkelanjutan

14. Tambahkan `composer audit` dan `npm audit` sebagai langkah CI. Keduanya belum ada;
    yang berjalan sekarang hanya pemeriksaan bawaan Composer saat memasang paket baru.
15. Bangun daftar aset dan pemiliknya.
16. Tinjau ulang kebijakan tiap ada fitur yang mengumpulkan data baru.

---

## 15. Pemisahan Tingkat Keyakinan

### VERIFIED

Seluruh isi bagian 3 sampai 12 berasal dari pembacaan langsung source code,
`composer.lock`, `package-lock.json`, konfigurasi, riwayat git, dan respons aplikasi
yang dijalankan lokal.

### INFERRED

- Tingkat risiko SVG bergantung pada cara web server menyajikan berkas unggahan.
  Belum diuji karena tidak ada server produksi.
- Dampak ketiadaan header keamanan diperkirakan dari praktik umum, bukan dari
  pengujian terhadap konfigurasi nyata.

### UNKNOWN

- Sertifikat TLS, konfigurasi web server, firewall, dan port terbuka.
- Apakah backup berjalan di tempat lain di luar repositori.
- Apakah perusahaan sudah memiliki Kebijakan Privasi di domain utama
  `hasnurgroup.com` yang dapat dipakai ulang.
- Apakah ada perjanjian magang, NDA, atau IP assignment yang ditandatangani di luar
  repositori.
- Apakah situs lama masih menyimpan data pengunjung yang perlu dimigrasikan atau
  dihapus.

### LEGAL REVIEW REQUIRED

1. Dasar pemrosesan mana yang dipilih untuk formulir kontak menurut Pasal 20.
2. Apakah perusahaan perlu menunjuk Pejabat Pelindungan Data.
3. Status hak cipta atas kode yang dihasilkan dengan bantuan AI.
4. Bentuk penyerahan hak dari peserta magang kepada perusahaan.
5. Masa simpan yang wajar untuk pesan dari calon mitra dan pelamar.
6. Apakah pencatatan rujukan berita pihak ketiga, berupa judul, media, tanggal, dan
   tautan, sudah cukup aman dari sisi hak cipta menurut penilaian legal perusahaan.

---

## 16. Pertanyaan untuk Manajemen

1. Siapa pengendali data untuk situs ini, PT Hasnur Group atau entitas lain?
2. Apakah sudah ada Kebijakan Privasi perusahaan yang berlaku untuk seluruh kanal
   digital?
3. Berapa lama pesan dari formulir kontak boleh disimpan?
4. Siapa yang berhak membaca pesan masuk, dan apakah perlu dibedakan dari pengelola
   konten?
5. Apakah repositori akan dipindahkan ke akun organisasi perusahaan?
6. Apakah peserta magang sudah menandatangani NDA dan penyerahan hak kekayaan
   intelektual?
7. Siapa yang menjadi penanggung jawab bila terjadi kebocoran data?

---

## 17. Status Penanganan, 24 September 2026

Ditambahkan setelah audit dikerjakan. Temuan di atas dibiarkan apa adanya sebagai
potret saat pemeriksaan.

| # | Temuan | Status | Cara penanganan |
|---|---|---|---|
| 1 | Tidak ada Kebijakan Privasi | **Sebagian** | Halaman `/page/legal/kebijakan-privasi` terbit, tertaut dari footer tiap halaman dan dari formulir, masuk sitemap tiga bahasa. Tiga bagian bertanda `[perlu keputusan]` menunggu jawaban manajemen dan legal |
| 3 | `.env.example` membawa `APP_DEBUG=true` | **Selesai** | Lihat catatan di bawah |
| 5 | Tidak ada header keamanan HTTP | **Selesai** | `SecurityHeaders` middleware, berlaku untuk seluruh permintaan web |
| 6 | Cookie sesi tidak dipaksa HTTPS | **Selesai** | `SESSION_SECURE_COOKIE=true` di template produksi, dan deploy menolak berjalan bila nilainya bukan itu |
| 8 | Unggahan menerima SVG tanpa batas ukuran | **Selesai** | Dibatasi ke jpeg, png, webp dengan batas 4 MB pada keempat medan unggahan |

Lima pemeriksaan baru di `ComplianceTest` menahan keempatnya, karena semuanya jenis
kerusakan yang membuat halaman tetap menjawab 200: header yang tercabut saat
middleware disederhanakan, tautan kebijakan yang hilang saat footer diubah, dan
pembatasan unggahan yang lenyap saat form disusun ulang.

### Catatan: temuan #3 ditangani berbeda dari rekomendasinya

Rekomendasi semula adalah mengubah `backend/.env.example` menjadi nilai produksi.
Itu tidak dikerjakan, karena berkas yang sama juga disalin CI dan mesin pengembang.
`SESSION_SECURE_COOKIE=true` di sana membuat cookie sesi tidak pernah terkirim lewat
http, sehingga login panel admin gagal pada `php artisan serve` dan seluruh
pemeriksaan panel admin ikut merah. Memperbaiki satu risiko dengan menciptakan
kegagalan yang pasti bukan pertukaran yang baik.

Yang dikerjakan sebagai gantinya dua hal. Pertama, `deploy/env.production.example`
sebagai template khusus server, lengkap dengan `APP_ENV=production`,
`APP_DEBUG=false`, `LOG_LEVEL=error`, `SESSION_ENCRYPT=true`, dan
`SESSION_SECURE_COOKIE=true`. Kedua, `deploy/after-deploy.sh` memeriksa keempat nilai
itu di server dan **menghentikan deploy** bila tidak sesuai, termasuk bila `APP_KEY`
masih kosong.

Perbedaannya bukan gaya. Template yang benar hanya menolong orang yang membacanya;
penjaga di skrip deploy menolong juga ketika tidak ada yang membaca. Diuji dengan
menjalankannya memakai `.env` pengembangan, dan deploy berhenti dengan menyebut
ketiga nilai yang salah.

### CSP sengaja belum ditegakkan

`Content-Security-Policy` dikirim sebagai `Report-Only`, bukan penegakan. Alpine
mengevaluasi ekspresi `x-data` lewat `new Function()`, panel admin memuat tiga blok
skrip inline dari Livewire, dan beranda memuat 102 atribut `style` inline yang
sebagian membawa warna merek dari database. Menegakkan kebijakan tanpa memeriksanya
lebih dulu di peramban berarti menukar risiko XSS dengan risiko situs yang tidak
berfungsi. Langkah berikutnya: buka situs dan panel admin di peramban, periksa
laporan pelanggaran di konsol, lalu ganti nama headernya menjadi
`Content-Security-Policy`.

---

## 18. Status Penanganan P1, 24 September 2026

| # | Temuan | Status | Cara penanganan |
|---|---|---|---|
| 7 | Semua akun admin berwenang penuh | **Selesai** | Dua peran, Administrator dan Editor Konten. Editor tidak dapat membuka pesan masuk maupun pengelolaan pengguna, ditutup di menu sekaligus di URL-nya |
| 10 | Tidak ada retensi dan penghapusan | **Selesai secara teknis** | `ContactMessage` dapat dipangkas, dijadwalkan harian, masa simpan diatur lewat `CONTACT_RETENTION_MONTHS`. Angkanya masih menunggu konfirmasi legal |
| 4 | Tidak ada prosedur penanganan kebocoran | **Sebagian** | `docs/prosedur-kebocoran-data.md` lengkap dengan tenggat 3x24 jam, tahapan, dan format pencatatan. Empat nama penanggung jawab masih kosong |
| 11 | Tidak ada backup | **Selesai** | `deploy/backup.sh`, harian lewat cron, disimpan 30 hari. Pemulihannya diuji dan terbaca |
| 12 | Tidak ada NDA dan prosedur offboarding | **Belum** | Di luar jangkauan teknis. Tetap menjadi pertanyaan untuk manajemen |

### Catatan atas masing-masing

**Peran.** Migrasi menaikkan akun yang sudah ada menjadi Administrator, bukan Editor.
Menurunkan hak akun yang sedang dipakai berarti mengunci orang di luar panelnya
sendiri pada saat peran diperkenalkan. Akun baru bawaannya Editor, jadi hak penuh
harus diberikan dengan sengaja. Menu Pengguna ditambahkan supaya peran dapat diatur
tanpa membuka tinker, dan menu itu sendiri hanya terlihat oleh Administrator: siapa
pun yang bisa membuat akun bisa membuat akun Administrator.

**Retensi.** Bila `CONTACT_RETENTION_MONTHS` disetel 0, pemangkasan dimatikan, bukan
berubah menjadi pemangkasan tanpa syarat. Salah ketik pada satu setelan tidak boleh
berujung pada terhapusnya seluruh pesan. Jadwalnya membutuhkan satu baris cron di
server; tanpa itu perintahnya tidak pernah berjalan, dan tidak ada yang akan
memberitahu. Sesi kedaluwarsa tidak ikut dijadwalkan karena driver database Laravel
sudah membersihkannya sendiri lewat `session.lottery`.

**Backup.** Yang dicadangkan hanya dua hal yang tidak ada di repositori mana pun: isi
basis data dan gambar yang diunggah dari panel. Kode, `vendor`, dan varian WebP tidak
ikut karena semuanya dapat dibangun ulang. Pemulihannya diuji pada tanggal audit dan
terbaca: 1 pesan, 36 artikel, 1 pengguna, 138 berkas unggahan. Skripnya sempat berhenti
diam-diam pada percobaan pertama karena `set -o pipefail` membuat `grep` yang tidak
menemukan `DB_DATABASE` mematikan seluruh skrip; itulah alasan pengujian pemulihan
dilakukan, bukan sekadar menjalankan skripnya sampai selesai tanpa galat.

### Yang masih terbuka setelah P1

1. Angka masa simpan pesan, menunggu legal.
2. Empat nama penanggung jawab pada prosedur kebocoran.
3. Tiga bagian `[perlu keputusan]` pada Kebijakan Privasi.
4. NDA, IP assignment, dan pemindahan repositori ke akun organisasi.
5. Satu baris cron untuk `schedule:run` dan satu untuk `backup.sh` di server, yang
   baru bisa dipasang setelah servernya ada.

---

## 19. Adendum: pengukuran penggunaan halaman, 29 September 2026

Ditambahkan setelah audit dikerjakan. Bagian 1 sampai 16 dibiarkan apa adanya
sebagai potret saat pemeriksaan; bagian ini mencatat satu pemrosesan data baru
yang belum ada pada tanggal itu, sehingga jawaban "tidak ada analitik" pada
bagian 4, 10, dan 11 tidak lagi berlaku.

**Apa yang berubah.** Situs kini mencatat sendiri empat peristiwa penggunaan
(CMS-10 pada TOR): klik tombol ajakan, unduhan berkas, pengiriman formulir
pencarian, dan artikel yang dibaca sampai bagian bawahnya. Tabel baru
`analytics_events`. Tidak ada skrip pihak ketiga yang dimuat untuk itu, dan tidak
ada data yang meninggalkan server situs; pemuat GA4/GTM tetap mati sampai ID-nya
diisi, terpisah dari pencatatan ini.

**Tambahan pada inventaris data (bagian 4).**

| Tabel | Kolom | Sumber | Klasifikasi | Catatan |
|---|---|---|---|---|
| `analytics_events` | `name`, `label`, `path`, `locale`, `created_at` | pengunjung | bukan data pribadi | tidak memuat pengenal orang; `label` berisi nama tombol, nama berkas, kata kunci pencarian, atau judul artikel |
| `analytics_events` | `visitor` | pengunjung | pseudonim | HMAC-SHA256 dari IP + User-Agent + tanggal hari itu, dipotong 32 karakter, kunci `APP_KEY` |

Alamat IP dan User-Agent **tidak** disimpan apa adanya, berbeda dari
`contact_messages` dan `sessions`. Kolom `visitor` berganti nilai tiap tengah
malam karena tanggalnya ikut masuk ke dalam HMAC, sehingga catatan hari berbeda
tidak dapat dihubungkan menjadi satu riwayat, dan tidak dapat dihitung ulang dari
luar tanpa `APP_KEY`. Kegunaannya terbatas pada satu hal: membedakan sepuluh klik
dari satu orang dengan sepuluh klik dari sepuluh orang dalam satu hari yang sama.

**Kata kunci pencarian adalah bagian yang perlu diperhatikan.** Medan pencarian
bersifat bebas, sehingga pengunjung secara teknis dapat menuliskan apa pun ke
dalamnya, termasuk namanya sendiri. Ini risiko yang sama bentuknya dengan medan
pesan bebas pada formulir Hubungi Kami dan ditangani dengan cara yang sama: lewat
kebijakan dan masa simpan, bukan lewat kode. Bedanya, catatan pencarian tidak
membawa satu pun pengenal untuk menghubungkannya dengan orang.

**Kebijakan privasi (bagian 10).** Pasal "Pengukuran penggunaan halaman"
ditambahkan ke `/page/legal/kebijakan-privasi`, dan pernyataan lama diperbaiki
menjadi "tidak memasang layanan analitik **pihak ketiga**". Pernyataan sebelumnya
akan menjadi tidak benar setelah perubahan ini, dan kebijakan privasi yang tidak
benar lebih buruk daripada kebijakan privasi yang belum lengkap.

**Retensi (bagian 11).** Dua belas bulan, `ANALYTICS_RETENTION_MONTHS`, dijalankan
`model:prune` oleh penjadwal harian — jadi ia ikut mati bila baris cron
`schedule:run` tidak ada di server, sama seperti pemangkasan pesan. Diisi 0
berarti tanpa batas.

**Jawaban yang berubah pada tabel bagian 11:** "Apakah analitik menyimpan
pengenal?" — kini ada pencatatan sendiri, dan jawabannya: tidak menyimpan
pengenal langsung; satu kolom pseudonim harian yang tidak dapat dibalik.

**Yang masih terbuka.** Dasar pemrosesan menurut Pasal 20 untuk catatan ini belum
dinyatakan, sama seperti untuk `contact_messages`. Penilaian teknis: kepentingan
sah pengendali, karena tidak ada pengenal orang yang disimpan dan tujuannya
terbatas pada perbaikan situs. Itu penilaian teknis, bukan opini hukum, dan tetap
perlu disahkan bersama tiga bagian `[perlu keputusan]` yang sudah terdaftar.
