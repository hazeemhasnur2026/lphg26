# Status Pemenuhan CMS — TOR Web Development Hasnur Group 2026 (R1)

Rincian per butir sheet **05_CMS**: apa yang diminta, apa yang dibangun,
bagaimana cara kerjanya, dan apa yang masih harus dilakukan siapa.

Per 29 September 2026. Aplikasi Laravel 13 + Filament 4, 146 tes otomatis,
seluruhnya lulus. Bukti visual tiap butir ada di folder `screenshot_cms_tor`.

## Ringkasan

| ID | Butir | Status | Sisa pekerjaan | Pemilik |
|---|---|---|---|---|
| CMS-01 | Admin Dashboard | ✅ Selesai | — | — |
| CMS-02 | Role & Permission | ✅ Selesai | Tetapkan peran tiap orang | CorpComm |
| CMS-03 | Workflow & audit trail | ✅ Selesai | — | — |
| CMS-04 | Content Types | ✅ Selesai | — | — |
| CMS-05 | Media Library | ✅ Selesai | Jalankan `media:import` di server | IT |
| CMS-06 | SEO Fields | ✅ Selesai | Tulis meta description artikel lama | CorpComm |
| CMS-07 | Scheduling | ✅ Selesai | — | — |
| CMS-08 | Search & Taxonomy | ✅ Selesai | Sepakati daftar tag baku | CorpComm |
| CMS-09 | Versioning | ✅ Selesai | — | — |
| CMS-10 | Analytics | ⚠️ Separuh | Pilih platform, isi ID-nya | IT |
| CMS-11 | Backup | ⚠️ Separuh | Salinan luar mesin; sahkan RPO/RTO | IT |
| CMS-12 | Training | ⚠️ Separuh | Jadwalkan dua sesi pelatihan | CorpComm |

Sembilan selesai penuh. Tiga sisanya menunggu keputusan atau tindakan di luar
kode: satu nama platform, satu penyimpanan, satu jadwal.

---

## CMS-01 — Admin Dashboard

**Diminta.** Dashboard ringkas untuk aktivitas konten dan penerbitan, mudah bagi
pengguna Corporate Communication yang bukan orang teknis.

**Dibangun.** Enam kartu angka dan satu tabel aktivitas.

| Kartu | Menjawab |
|---|---|
| Menunggu tinjauan | Ada naskah yang menunggu dibaca |
| Draf | Belum diajukan siapa pun |
| Terjadwal | Akan terbit sendiri |
| Tanpa tanggal | Artikel migrasi yang tanggalnya belum dipastikan |
| Gambar tanpa alt text | Muncul hanya bila ada |
| Pesan belum ditangani | Hanya untuk Admin Korporat ke atas |

Isinya dipilih untuk menjawab "apa yang perlu saya kerjakan hari ini", bukan
"berapa banyak yang kita punya". Jumlah total artikel tidak pernah mengubah
keputusan siapa pun; naskah yang menunggu sejak kemarin mengubahnya.

Widget bawaan Filament yang berisi tautan dokumentasi dan nomor versi dilepas.
Berguna bagi yang membangun panel, tidak bagi yang memakainya tiap hari, dan ia
menempati ruang paling atas.

**Status:** selesai. **Sisa:** tidak ada.

---

## CMS-02 — Role & Permission

**Diminta.** Minimal empat peran: Super Admin, Corporate Communication
Admin/Editor, Contributor, Reviewer/Approver. Akses berbasis peran.

**Dibangun.** Empat peran, dibedakan oleh satu pertanyaan: seberapa jauh sebuah
naskah boleh didorong oleh orang ini sendirian.

| Peran | Menulis | Mengajukan | Menyetujui & menerbitkan | Pesan masuk | Kelola akun |
|---|:-:|:-:|:-:|:-:|:-:|
| Kontributor | naskah sendiri | ✓ | — | — | — |
| Peninjau | semua | ✓ | ✓ | — | — |
| Admin Korporat | semua | ✓ | ✓ | ✓ | — |
| Super Admin | semua | ✓ | ✓ | ✓ | ✓ |

Pembatasan kontributor ada **di query**, bukan di tampilan. Menyembunyikan baris
dari tabel tidak menutup URL `/admin/posts/{slug}/edit`, dan alamat yang bisa
ditebak adalah undangan untuk mencoba.

Kontributor juga berhenti bisa menyunting naskahnya sendiri begitu diajukan:
naskah yang sedang dibaca peninjau tidak boleh berubah di bawah tangannya.

**Status:** selesai.

**Sisa — CorpComm.** Migrasi menaikkan semua akun lama menjadi Super Admin,
bukan menurunkannya. Itu disengaja: menurunkan hak akun yang sedang dipakai akan
mengunci orang di luar panelnya sendiri tepat saat sistem peran diperkenalkan.
Akibatnya hari ini semua orang bisa melakukan segalanya.

**Yang harus dilakukan:** putuskan siapa menulis, siapa menyetujui, dan siapa
boleh membaca pesan pengunjung. Lalu Super Admin membuka **Pengaturan →
Pengguna** dan menetapkan peran tiap orang. Akun baru bawaannya Kontributor.

---

## CMS-03 — Workflow

**Diminta.** Draft → Review → Approval → Publish → Update/Archive. Audit trail
wajib tersedia.

**Dibangun.**

```
Draf ──ajukan──► Menunggu Tinjauan ──setujui──► Disetujui ──terbitkan──► Terbit
                        │                           │                      │
                        └────── kembalikan ─────────┴──────────────────────┘
                              (ke Draf, wajib pakai alasan)        Terbit ──► Diarsipkan
```

**Alurnya tidak bisa dilompati, juga oleh Super Admin.** Perpindahan yang sah
didaftar di satu tempat (`PostStatus::nextStates()`) dan dijalankan hanya lewat
satu kelas (`PostWorkflow`), sehingga tidak ada jalan memindahkan status tanpa
ikut meninggalkan jejak audit.

`status` dan `published_at` sengaja terpisah. Keduanya menjawab hal berbeda dan
boleh berselisih dengan sah: artikel berstatus Terbit dengan tanggal di masa
depan adalah artikel terjadwal yang belum tampil.

**Jejak audit** mencatat pembuatan, penyuntingan, perpindahan status,
pengembalian versi, dan penghapusan — siapa, apa, kapan, dari IP mana. **Tidak
bisa disunting dan tidak bisa dihapus siapa pun**, termasuk Super Admin. Nama
pelaku ikut disalin saat peristiwa terjadi, jadi jejaknya tetap terbaca setelah
akunnya dihapus.

**Pemberitahuan.** Lonceng di panel memberi tahu orang yang giliran berikutnya
jadi miliknya: peninjau saat naskah diajukan, penulis saat naskah dikembalikan
(berikut alasannya), disetujui, atau terbit. Pengarsipan tidak memberi tahu siapa
pun — itu keputusan redaksi atas konten lama, bukan giliran orang lain.

Lewat lonceng, bukan surel: surel butuh kredensial SMTP yang belum tentu ada saat
panel dipasang, dan yang gagal terkirim karena `MAIL_HOST` kosong tidak
meninggalkan jejak yang terlihat siapa pun. Surel bisa ditambahkan belakangan
tanpa mengubah alurnya.

**Status:** selesai. **Sisa:** tidak ada.

---

## CMS-04 — Content Types

**Diminta.** News, Press Release, Story, Insight, Publication, Report, Gallery,
Video, Business, Sustainability, Career. Model konten terstruktur.

**Dibangun.** Sembilan tipe pada model artikel, ditambah dua yang sejak awal
punya modelnya sendiri: Business (Unit Bisnis) dan Career (Lowongan).

Tipe dibedakan dari **kategori**, dan keduanya hidup berdampingan:

- **Kategori** menentukan URL artikel dan halaman daftar tempat ia muncul.
  Tidak diganti karena 35 artikel migrasi sudah terindeks Google dengan bentuk
  URL itu.
- **Tipe** menentukan bentuk kontennya. Satu daftar boleh memuat beberapa tipe.

Empat tipe punya tampilan sendiri di halaman publik:

| Tipe | Yang dirender |
|---|---|
| Laporan, Publikasi | Tombol unduh dengan jenis dan ukuran berkas |
| Video | Pemutar tertanam (YouTube, Vimeo) atau `<video>` untuk berkas sendiri |
| Galeri | Kisi foto dari Pustaka Media, dengan lightbox |

Isi khas tipe ditaruh **di atas** badan artikel: untuk Laporan, badan artikel
adalah pengantar, dan yang dicari pembaca adalah berkasnya.

Tautan video diterjemahkan saat ditampilkan, bukan saat disimpan — yang tersimpan
tetap tautan yang redaksi salin dari bilah alamat. Penyedia yang tidak dikenali
dijawab tautan biasa, bukan iframe kosong: kotak abu-abu tanpa galat adalah
bentuk kerusakan yang paling lama tidak ketahuan.

**Status:** selesai. **Sisa:** tidak ada.

---

## CMS-05 — Media Library

**Diminta.** Pengelolaan aset terpusat dengan metadata, alt text, dan
folder/tagging. Mendukung gambar, video, dan PDF.

**Dibangun.** Menu **Pustaka → Pustaka Media**, berisi gambar, video, dan PDF
dengan judul, alt text, kredit, folder, dan tag. Satu berkas bisa dipakai di
banyak artikel; galeri mengambil dari pustaka, bukan mengunggah ulang.

Folder disimpan sebagai teks datar, bukan pohon berjenjang. Yang dibutuhkan
redaksi adalah pengelompokan seperti `csr-2026`, dan pohon membawa pertanyaan
yang tidak ada yang mau jawab: apa yang terjadi pada isinya saat folder induk
dihapus.

Penyaring **Belum ada alt text** menunjukkan gambar yang masih berhutang
aksesibilitas; angkanya juga muncul di dasbor.

**Status:** selesai.

**Sisa — IT.** Gambar dari situs lama sudah dilayani ke pembaca sejak hari
pertama, tetapi belum punya barisnya di pustaka, jadi belum bisa dicari atau
dipakai ulang.

**Yang harus dilakukan:** jalankan sekali di server:

```sh
docker compose exec app php backend/artisan media:import
```

Perintah ini tidak memindahkan dan tidak mengubah berkas apa pun, hanya mencatat
apa yang sudah ada, dan melewati yang barisnya sudah ada — menjalankannya dua
kali tidak menggandakan apa pun. Tambahkan `--dry` untuk melihat lebih dulu.

Alt text sengaja dibiarkan kosong. Menebaknya dari nama berkas menghasilkan
"dsc-0042" sebagai keterangan bagi pengguna pembaca layar — lebih buruk daripada
kosong, karena yang kosong muncul di penyaringnya dan yang asal-asalan tidak.

---

## CMS-06 — SEO Fields

**Diminta.** Meta title, meta description, URL slug, OG image, canonical,
kontrol sitemap. Dapat disunting pengguna berwenang.

**Dibangun.** Semuanya bisa disunting, masing-masing punya cadangan yang masuk
akal bila dikosongkan:

| Kolom | Bila kosong |
|---|---|
| Judul SEO | memakai judul artikel |
| Meta description | memakai ringkasan |
| Gambar berbagi (OG) | memakai gambar sampul |
| URL kanonik | memakai URL artikel sendiri |

Sakelar **Jangan indeks halaman ini** mengeluarkan artikel dari sitemap sekaligus
memasang `robots: noindex`. Keduanya, bukan salah satu: sitemap hanya usulan —
halaman tetap bisa ditemukan lewat tautan, dan hanya meta robots yang benar-benar
meminta mesin pencari melewatinya.

Sitemap dibangun dari rute dan basis data, dan sejak sekarang hanya memuat
artikel yang benar-benar tampil. Nilai tambahan di luar TOR: JSON-LD
`NewsArticle` pada tiap halaman artikel.

**Status:** selesai.

**Sisa — CorpComm.** 35 artikel migrasi belum punya meta description sendiri,
jadi jatuh ke ringkasan. Situs tetap benar; yang hilang adalah kendali atas apa
yang terbaca di hasil pencarian.

**Yang harus dilakukan:** tulis meta description ±150 karakter. Tidak perlu 35
sekaligus — dahulukan artikel yang paling banyak dibuka. Ringkasan dan meta
description ditulis untuk pembaca berbeda: yang satu untuk orang yang sudah ada
di halaman daftar, yang lain untuk orang yang belum memutuskan mengklik.

---

## CMS-07 — Scheduling

**Diminta.** Jadwal terbit dan berhenti tampil untuk konten tertentu. Zona waktu
Asia/Jakarta.

**Dibangun.** `published_at` boleh diisi masa depan; artikel baru muncul setelah
waktu itu lewat, dan daftar di panel menandainya *terjadwal*. Pasangannya
`unpublish_at`, untuk konten yang harus berhenti tampil sendiri.

Zona waktu aplikasi disetel `Asia/Jakarta`, dan jam container disamakan dengan
jam aplikasi. Tanpa itu, nama berkas backup bertimestamp tujuh jam lebih awal
daripada yang tertulis di panel — tepat saat seseorang harus memilih backup mana
yang dipulihkan.

**Tidak butuh cron.** Jadwalnya dievaluasi saat halaman diminta, bukan oleh
proses latar. Tidak ada yang bisa lupa berjalan.

**Status:** selesai. **Sisa:** tidak ada.

---

## CMS-08 — Search & Taxonomy

**Diminta.** Kategori, tag, penulis, tanggal, sektor bisnis, dan tipe konten.
Dapat disaring dan skalabel.

**Dibangun.** Penyaring lengkap di panel — status, tipe, kategori, penulis, tag,
unit bisnis, rentang tanggal — dan halaman pencarian publik di `/cari` dengan
penyaring tipe dan tag, dapat diakses lewat ikon di navigasi.

Tag disimpan sebagai tabel tersendiri, bukan kolom teks berisi daftar dipisah
koma. Bentuk kedua lebih cepat dibuat dan langsung menyulitkan hal yang justru
jadi alasan tag ada: "energi" dan "Energi" menjadi dua hal, dan mengganti nama
sebuah tag berarti menyunting tiap artikel.

Pencariannya memakai `LIKE`, bukan mesin pencari tersendiri. Isi situs ini
puluhan artikel, bukan puluhan ribu, dan SQLite menjawabnya dalam milidetik.
Memasang Meilisearch berarti satu layanan lagi yang harus menyala, dicadangkan,
dan diperbarui, demi kecepatan yang tidak akan terasa. Yang perlu diawasi adalah
kapan alasan ini berhenti berlaku: bila artikel menembus ribuan, jawabannya bukan
mengakali `LIKE` melainkan mengganti lapisan ini.

**Status:** selesai.

**Sisa — CorpComm.** Daftar tag baku belum ditetapkan, dan penulis artikel
migrasi masih kosong karena importer tidak tahu siapa penulisnya.

**Yang harus dilakukan:** sepakati 10–15 tag baku sebelum redaksi mulai memberi
tag. Kalau dibiarkan bebas, akan lahir "CSR", "csr", dan "Tanggung Jawab Sosial"
sebagai tiga hal berbeda, dan penyaringnya berhenti berguna justru saat artikelnya
sudah banyak.

---

## CMS-09 — Versioning

**Diminta.** Riwayat revisi dan pengembalian. Catat siapa mengubah apa dan kapan.

**Dibangun.** Tab **Riwayat Versi** pada tiap artikel, bernomor per artikel —
yang ditanya orang adalah "versi ke berapa", dan id global menjawabnya dengan
angka yang melompat-lompat karena dipakai bersama artikel lain.

Versi dibuat hanya bila ada kolom naskah yang benar-benar berubah. Membuka
artikel lalu menekan Simpan tanpa mengubah apa pun tidak menambah versi; riwayat
penuh versi kembar membuat yang asli tidak bisa ditemukan.

**Pengembalian bisa dibatalkan.** Isi yang sedang berlaku disimpan dulu sebagai
versi baru, lalu isi lama ditulis di atasnya. Yang dikembalikan hanya naskahnya —
status, tanggal terbit, dan catatan tinjauan tidak ikut, karena mengembalikan
tulisan bukan alasan membatalkan persetujuan orang lain.

**Masa simpan:** 50 versi terakhir per artikel, dipangkas otomatis tiap pekan,
diatur lewat `REVISION_RETENTION` di `.env` (isi 0 untuk mematikan). Batasnya per
jumlah, bukan per umur: artikel yang disunting sepuluh kali sebulan akan
kehilangan seluruh riwayatnya sekaligus kalau batasnya umur, sementara yang
disunting setahun sekali menyimpan satu versi selamanya.

**Status:** selesai. **Sisa:** tidak ada.

---

## CMS-10 — Analytics

**Diminta.** Integrasi dengan platform analitik yang disetujui. Event untuk CTA,
unduhan, pencarian, dan keterlibatan konten.

**Dibangun.** Keempat peristiwa yang diminta, dikirim ke dua penerima sekaligus:

| Event TOR | Nama | Kapan terkirim |
|---|---|---|
| CTA | `cta_click` | tombol bertanda `data-cta` diklik |
| Downloads | `file_download` | tautan PDF/DOCX/XLSX/ZIP diklik |
| Search | `search` | formulir pencarian dikirim, berikut kata kuncinya |
| Content engagement | `content_read` | pembaca sampai ke ujung bawah artikel |

Ditambah satu yang ikut sejak awal karena datanya memang sudah ada:
`outbound_click`, saat pengunjung membuka tautan ke domain lain.

Dipasang lewat satu pendengar di `document`, bukan atribut `onclick` di tiap
tombol. Tombol datang dari belasan view dan sebagian dirender Alpine setelah
halaman dimuat; atribut yang ditempel satu per satu akan terlewat persis di
tombol yang baru ditambahkan orang.

### Penerima pertama: platform luar

`dataLayer`, dibaca GA4 atau GTM. **Mati secara bawaan** dan sengaja begitu:
skrip pengukuran pihak ketiga yang ikut menyala di mesin pengembang dan server
uji akan mencampur kunjungan tim sendiri ke dalam angka yang dipakai mengambil
keputusan, dan tidak ada yang menyadarinya sampai angkanya dipertanyakan.

### Penerima kedua: papan analitik situs sendiri

Menu **Analitik** di panel, di bawah Pengaturan. Peristiwa yang sama disimpan di
tabel `analytics_events` pada basis data situs, dan papannya memperlihatkan:

| Bagian | Isinya |
|---|---|
| Kartu ringkasan | jumlah tiap peristiwa 30 hari terakhir, pengunjung unik, angka hari ini, grafik kecil 7 hari |
| Grafik harian | satu garis per jenis peristiwa, rentang 7 / 30 / 90 hari |
| Lima tabel peringkat | CTA, berkas, kata kunci, artikel, dan tautan keluar yang paling sering |

Ini yang membuat butir ini punya angka yang bisa dilihat hari ini. Sebelumnya
keempat peristiwa memang sudah terkirim ke `dataLayer`, tapi `dataLayer` tanpa
platform yang membacanya berarti tidak ada satu pun angka yang terlihat siapa
pun — dan platformnya menunggu keputusan di luar kode.

Papan ini **tidak** menggantikan platform analitik. Yang tidak dijawabnya: dari
mana pengunjung datang, kata kunci apa yang membawanya dari mesin pencari,
pengunjung baru atau yang kembali, dan perbandingan dengan situs lain. Ia juga
ikut menghitung kunjungan tim sendiri. Ketiga batas itu tertulis di halamannya,
bukan hanya di dokumen ini: papan angka tanpa keterangan asal akan dibaca
sebagai angka Google Analytics oleh siapa pun yang belum membaca TOR-nya.

Yang boleh membukanya sama dengan yang boleh membaca jejak audit — Peninjau ke
atas. Kontributor tidak, karena papan ini memuat kata kunci yang diketik
pengunjung.

### Yang tidak disimpan

Alamat IP dan User-Agent apa adanya. Keduanya cukup untuk menunjuk satu orang,
dan tidak ada pertanyaan di papan itu yang membutuhkannya. Yang disimpan hanya
sidik pendek hasil HMAC keduanya bersama tanggal hari itu: berganti tiap tengah
malam, tidak bisa dikembalikan ke asalnya, tidak bisa dihubungkan dengan catatan
hari sebelumnya. Gunanya satu, membedakan sepuluh klik dari satu orang dengan
sepuluh klik dari sepuluh orang.

Kebijakan privasi situs sudah diperbarui mengikuti ini — pasal "Pengukuran
penggunaan halaman". Peristiwa yang lebih tua dari dua belas bulan dihapus
penjadwal harian; batasnya `ANALYTICS_RETENTION_MONTHS`.

### Data contoh untuk peninjauan

Papan kosong tidak bisa ditinjau: tidak ada yang bisa mengatakan kolom mana yang
kurang saat semuanya bernilai nol. Untuk memperlihatkan bentuknya sebelum ada
pengunjung yang mengklik apa pun:

```
php artisan analitik:contoh              # isi 30 hari terakhir
php artisan analitik:contoh --hapus      # buang kembali
```

Tiap baris contoh bertanda `demo`, dihitung terpisah, dan papannya memasang
peringatan mencolok selama masih ada satu pun di basis data. **Harus dibuang
sebelum situs dipakai sungguhan** — data contoh yang tidak dibedakan dari data
sungguhan berhenti menjadi contoh dan menjadi kekeliruan.

**Status:** separuh. Papan sendiri berjalan; integrasi ke platform yang disetujui
menunggu satu nilai.

**Sisa — IT.** Kata "approved" pada TOR adalah bagian yang tidak bisa diputuskan
dari sisi teknis.

**Yang harus dilakukan:** tanyakan platform mana yang disetujui, lalu isi **salah
satu** di `.env` produksi:

```
ANALYTICS_GA_ID=      # Google Analytics 4
ANALYTICS_GTM_ID=     # Google Tag Manager
```

Jangan isi keduanya. GTM sudah memuat GA dari dalam dirinya, dan dua pemuat
sekaligus menghitung tiap kunjungan dua kali. Papan Analitik di panel tidak
terpengaruh: ia tetap berjalan sebelum dan sesudah ID diisi.

---

## CMS-11 — Backup

**Diminta.** Prosedur backup dan restore otomatis. Target RPO/RTO diusulkan
vendor.

**Dibangun.** Backup harian 02.15 WIB, dijadwalkan di dalam aplikasi dan
dijalankan container penjadwal. Isinya dua hal yang tidak ada di tempat lain
mana pun: basis data dan gambar yang diunggah dari panel. Kode, vendor, dan
varian WebP tidak ikut — semuanya bisa dibangun ulang dari repositori.

Hasilnya masuk volume `cadangan`, **sengaja terpisah** dari volume basis data:
backup yang berada di volume yang sama akan ikut hilang tepat pada kejadian yang
membuatnya dibutuhkan. Retensi 30 hari, keluaran tiap jalannya ditulis ke
`storage/logs/backup.log`.

Prosedur pemulihan langkah demi langkah ada di
`docs/prosedur-backup-dan-pemulihan.md`, berikut cara memeriksa isi backup.

**Status:** separuh.

**Sisa — IT.** Tiga hal:

1. **Salinan di luar mesin.** Backup hari ini masih berada di host yang sama
   dengan situsnya. Ia melindungi dari basis data rusak, penghapusan tak
   sengaja, dan migrasi gagal — bukan dari host yang hilang. Sampai ini ada,
   RTO 4 jam hanya berlaku selama hostnya masih hidup.
2. **Sahkan RPO/RTO.** Usulan: RPO 24 jam, RTO 4 jam. Keduanya menentukan berapa
   banyak pekerjaan yang boleh hilang dan berapa lama situs boleh padam — itu
   keputusan pemilik situs, bukan keputusan teknis.
3. **Uji pemulihan sebulan sekali.** Perintahnya ada di dokumen. Backup yang
   gagal diam-diam sama dengan tidak punya backup, dan melihat berkasnya ada
   tidak cukup.

---

## CMS-12 — Training

**Diminta.** Pelatihan admin/editor dan manual CMS. Minimal dua sesi pelatihan
ditambah panduan pengguna.

**Dibangun.** Panduan pengguna lengkap di `docs/panduan-admin-cms.md`: peran dan
hak akses, alur sebuah artikel dari draf sampai arsip, penjadwalan, bagian SEO,
pustaka media, riwayat versi, jejak audit, dasbor, dan daftar pertanyaan yang
sering muncul.

**Status:** separuh — panduannya ada, pelatihannya belum.

**Sisa — CorpComm.** Jadwalkan dua sesi. Saran pembagian:

- **Sesi 1 — alur harian.** Menulis, mengajukan, meninjau, menerbitkan.
  Menjadwalkan terbit. Membaca pemberitahuan.
- **Sesi 2 — di sekitarnya.** Pustaka media dan alt text, bagian SEO, riwayat
  versi dan pengembalian, jejak audit.
