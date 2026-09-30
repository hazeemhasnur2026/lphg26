# Situs Korporat PT Hasnur Group

Peremajaan hasnurgroup.com. Aplikasi Laravel 13 dengan panel admin Filament,
tiga bahasa, dan pipeline gambar WebP. Prototipe statis yang menjadi rujukan
desain masih tersimpan di akar repositori.

| | |
|---|---|
| Kerangka | Laravel 13, PHP 8.4, Livewire 3, Alpine 3, Tailwind 4 |
| Panel admin | Filament 4 di `/admin`, 10 resource |
| Bahasa | Indonesia (bawaan), Inggris, Jepang |
| Basis data | SQLite, 19 migrasi, 11 model |
| Isi | 35 artikel hasil migrasi, 7 unit usaha, 3 kantor, 5 banner |
| Alur redaksi | Draf → Tinjau → Setujui → Terbit → Arsip, dengan jejak audit |
| Tes | 110, dijalankan tiap push |

## Menjalankan

### Docker

Cara tercepat, tanpa memasang PHP, Node, maupun basis data di mesin sendiri.

```sh
cp deploy/env.production.example backend/.env
docker compose run --rm app php artisan key:generate --show   # salin ke APP_KEY
docker compose up -d --build
```

Situs di `http://localhost:8080`, panel di `/admin`. Porta diganti lewat
`PORT_SITUS`.

Penyalaan pertama mengisi basis datanya sendiri: seeder untuk unit usaha,
kantor, dan banner, lalu `import:articles` untuk artikel hasil crawl. Penyalaan
berikutnya melewati langkah ini karena berkasnya sudah ada di volume.

Akun panel pertama:

```sh
docker compose exec app php artisan make:filament-user
```

### Langsung di mesin

```sh
cd backend
composer install
php artisan migrate --seed
npm ci && npm run build
php artisan serve
```

`npm run build` wajib. Tailwind dan Alpine tidak lagi diambil dari CDN dan
`public/build/` tidak dilacak git, jadi `@vite` melempar galat sampai hasil
build-nya ada. Rangkaian tes tidak terpengaruh karena `TestCase` memakai
`withoutVite()`.

## Kebutuhan

| | Versi | Untuk |
|---|---|---|
| PHP | 8.4 ke atas | aplikasi |
| Composer | 2.x | dependensi PHP |
| Node | 22 ke atas | membangun CSS dan JS |
| SQLite atau MySQL | | basis data |
| `cwebp` dan `sips` | | hanya saat mengonversi gambar baru |

PHP 8.3 tidak cukup. Laravel 13 membawa Symfony 8, dan bersama plugin
terjemahan Filament ada 18 paket terkunci yang menuntut 8.4. Hosting PHP 8.3
gagal saat `composer install`.

`sips` bawaan macOS, jadi konverter gambar belum bisa berjalan di Linux. Ini
tidak menghalangi deploy karena variannya sudah dilacak git.

## Struktur repositori

```
backend/            aplikasi Laravel: situs publik dan panel admin
  app/Support/      pembaca manifest gambar, JSON-LD, daftar URL pensiun
  app/Filament/     panel admin, satu resource per model
  resources/views/  30 berkas Blade, komponen x-img dan x-page-hero
assets/             gambar dan CSS bersama, dipakai aplikasi dan prototipe
  img-opt/          385 varian WebP, dilacak git
content-migration/  hasil crawl situs lama, gambar aslinya tidak dilacak
  images-opt/       249 varian WebP artikel, dilacak git
data/               manifest gambar dan daftar URL hasil crawl
docker/             nginx, supervisor, dan entrypoint container
tools/              konverter gambar, sapuan QA, pemeriksa cakupan CSS
scripts/            crawler artikel situs lama
deploy/             langkah yang dijalankan di server setelah deploy
docs/               spesifikasi, brief konten, laporan, audit kepatuhan
arsip/              bahan rujukan desain, tidak dirujuk kode mana pun
```

Prototipe statis (`index.html`, `page/`, `robots.txt`, `sitemap.xml` di akar)
sudah dihapus setelah seluruh 61 artikel dan halaman tetapnya berhasil pindah
ke `backend/`. `assets/` tetap di akar: dilayani aplikasi lewat symlink
(`deploy/link-public-dirs.sh`), jadi bukan sisa prototipe walau letaknya sama.

## Panel admin

| Menu | Mengelola | Tampil di |
|---|---|---|
| Publikasi, Update Terbaru / Berita / Press Release | `posts` | daftar publikasi, halaman artikel, kartu beranda, footer, sitemap |
| CSR, Aktivitas CSR | `posts` | `/page/csr/csr-activity` |
| SHE, Aktivitas SHE | `posts` | `/page/she/she-activity` |
| Konten Situs, Unit Bisnis | `business_units` | 7 kartu SBU di beranda |
| Konten Situs, Slider Beranda | `slide_banners` | slider galeri beranda |
| Konten Situs, Kantor | `offices` | alamat di footer |
| Karir, Lowongan | `vacancies` | halaman Lowongan Kami |
| Publikasi, Pesan Masuk | `contact_messages` | Admin Korporat ke atas |
| Pustaka, Pustaka Media | `media` | galeri artikel, dipakai ulang lintas artikel |
| Pustaka, Tag | `tags` | penyaring di panel dan di halaman `/cari` |
| Pengaturan, Analitik | `analytics_events` | papan angka klik, unduhan, pencarian, dan artikel yang dibaca tuntas |
| Pengaturan, Jejak Audit | `audit_logs` | baca saja, tidak bisa dihapus siapa pun |

Kelima kategori artikel punya menunya sendiri, mengikuti bentuk navigasi situs
publik. Membuka satu menu berarti melihat daftar kategori itu saja, dan tombol
Buat di situ langsung membuat artikel dengan kategori yang sama. Kategorinya
tetap bisa diubah dari dalam form.

Empat peran, ditentukan `App\Enums\UserRole`: Super Admin, Admin Korporat,
Peninjau, dan Kontributor. Pembedanya satu pertanyaan — seberapa jauh sebuah
naskah boleh didorong oleh orang ini sendirian. Kontributor menulis dan
mengajukan tapi tidak bisa menerbitkan, dan hanya melihat naskahnya sendiri;
pembatasan itu ada di query, bukan sekadar di tampilan, jadi URL tebakan pun
tertutup. Akun baru bawaannya Kontributor.

Alur redaksinya tidak bisa dilompati, juga oleh Super Admin: perpindahan status
yang sah didaftar di `PostStatus::nextStates()` dan dijalankan hanya lewat
`App\Support\PostWorkflow`, sehingga tidak ada jalan memindahkan status tanpa
ikut meninggalkan jejak audit.

`status` dan `published_at` sengaja terpisah. Keduanya menjawab hal berbeda dan
boleh berselisih: artikel berstatus Terbit dengan tanggal di masa depan adalah
artikel terjadwal, belum tampil. Pasangannya `unpublish_at`, untuk konten yang
harus berhenti tampil sendiri.

Riwayat versi ada di tab Riwayat Versi tiap artikel, lengkap dengan tombol
kembalikan. Yang dikembalikan hanya naskahnya — status dan tanggal terbit tidak
ikut, karena mengembalikan tulisan bukan alasan membatalkan persetujuan orang
lain.

Perpindahan status memberi tahu orang yang giliran berikutnya jadi miliknya —
peninjau saat naskah diajukan, penulis saat naskah dikembalikan, disetujui, atau
terbit. Pemberitahuannya lewat lonceng di panel, bukan surel: surel butuh
kredensial SMTP yang belum tentu ada saat panel dipasang, dan yang gagal terkirim
karena `MAIL_HOST` kosong tidak meninggalkan jejak yang terlihat siapa pun.

Pemberitahuannya dikirim dengan `notifyNow()`, bukan `sendToDatabase()`. Cara
kedua memanggil `notify()`, dan kelas notifikasi Filament mengimplementasikan
`ShouldQueue`: pemberitahuannya masuk tabel `jobs` dan menunggu pekerja antrean
yang tidak dijalankan aplikasi ini di mana pun.

Panduan pemakaian untuk redaksi ada di `docs/panduan-admin-cms.md`.

Akses panel ditentukan `User::canAccessPanel()`. Tanpa metode itu Filament
hanya mengizinkan akses saat `APP_ENV=local`, sehingga di produksi tidak ada
satu pun akun yang bisa masuk.

Berkas yang sudah ada di disk didaftarkan ke Pustaka Media dengan
`php artisan media:import`. Perintah ini tidak memindahkan dan tidak mengubah
berkas apa pun, hanya mencatat apa yang sudah ada, dan melewati yang barisnya
sudah ada — menjalankannya dua kali tidak menggandakan apa pun. Alt text
sengaja dibiarkan kosong: menebaknya dari nama berkas menghasilkan "dsc-0042"
sebagai keterangan bagi pengguna pembaca layar, dan yang kosong setidaknya
muncul di penyaring "Belum ada alt text".

Gambar yang diunggah belum otomatis menjadi WebP. Unggahan mendarat di disk
yang sama dengan gambar lama, jadi jalankan konverternya setelah mengunggah dan
variannya ikut terbentuk. Sebelum itu gambar baru tetap tampil, hanya memakai
berkas aslinya yang lebih berat.

Isi panjang tiap halaman unit bisnis masih view Blade. Kolom `full_content`
sengaja tidak dimunculkan di form supaya tidak terlihat seolah sudah bisa
dipakai.

## Deploy

Tiap push ke `main` menjalankan CI (`.github/workflows/ci.yml`): 146 tes, Pint,
dan build aset pada PHP 8.4. Deploy (`deploy.yml`) menyusul hanya bila CI lulus,
mengirim berkas lewat rsync/SSH lalu menjalankan `deploy/after-deploy.sh` di
server.

### Secrets

Settings, Secrets and variables, Actions:

| Secret | Isi |
|---|---|
| `DEPLOY_HOST` | alamat server |
| `DEPLOY_USER` | pengguna SSH |
| `DEPLOY_PATH` | folder tujuan di server, isi repo ditaruh langsung di sini |
| `DEPLOY_SSH_KEY` | kunci privat SSH, seluruhnya termasuk baris BEGIN dan END |
| `DEPLOY_PORT` | opsional, bawaan 22 |

Tanpa `DEPLOY_HOST`, workflow mencatat keterangan lalu melewati seluruh
langkahnya dan berakhir hijau tanpa menyentuh server mana pun.

Tidak ada kredensial yang boleh masuk repositori ini, sekalipun repositorinya
privat. Riwayat git tersimpan permanen dan hak akses bisa berubah.

### Persiapan sekali di server

1. Salin `backend/.env` dari `deploy/env.production.example`, lalu isi `APP_KEY`
   (`php artisan key:generate`), `APP_URL`, dan setelan SMTP. Berkas ini tidak
   pernah ikut terkirim. `deploy/after-deploy.sh` menolak melanjutkan bila
   `APP_ENV`, `APP_DEBUG`, `SESSION_SECURE_COOKIE`, atau `APP_KEY` tidak sesuai.
2. Arahkan document root ke `backend/public`.
3. Basis data. SQLite cukup untuk trafik situs ini, asalkan berkasnya di luar
   document root dan bisa ditulis. MySQL juga didukung, tinggal ganti `DB_*`.
4. Dua baris cron:

```
* * * * *   cd /path/ke/backend && php artisan schedule:run >> /dev/null 2>&1
```

Satu baris, bukan dua. Pekerjaan berkala dijadwalkan di dalam aplikasi
(`backend/routes/console.php`): backup tiap 02.15 WIB, pemangkasan pesan yang
melewati masa simpan (`CONTACT_RETENTION_MONTHS`, bawaan 24 bulan), pemangkasan
peristiwa analitik yang melewati masa simpan (`ANALYTICS_RETENTION_MONTHS`,
bawaan 12 bulan), dan pemangkasan riwayat versi tiap pekan (`REVISION_RETENTION`,
bawaan 50 versi per artikel). Semuanya ikut mati bila baris cron ini tidak ada,
dan tidak ada yang memberitahu.

Sasaran RPO/RTO, cara memeriksa isi backup, dan langkah pemulihan ada di
`docs/prosedur-backup-dan-pemulihan.md`.

`deploy/after-deploy.sh` mengurus sisanya tiap deploy: membuat ulang tiga
symlink di `backend/public/` yang tidak dilacak git, menyetel izin tulis,
menjalankan migrasi, lalu menyalakan cache config, rute, view, dan event.

Yang tidak ikut terhapus saat rsync: `.env`, berkas SQLite, folder unggahan, dan
gambar artikel hasil crawl. Keempatnya hidup di server dan tidak ada di repo.

Prosedur bila terjadi kebocoran data ada di `docs/prosedur-kebocoran-data.md`.

## Gambar

Gambar asli di repositori ini beresolusi kamera, beberapa 5000x3750 piksel dan
dua di antaranya lebih dari 5 MB. Yang dikirim ke peramban bukan berkas itu,
melainkan varian WebP yang sudah ada di repo bersama manifestnya,
`data/image-variants.json` dengan 224 entri. Clone dan deploy langsung
menyajikan gambar yang sudah dioptimalkan tanpa menjalankan apa pun.

Konverter hanya perlu dijalankan setelah menambah atau mengganti gambar sumber:

```sh
python3 tools/optimize_images.py --tree assets    # assets/img  -> assets/img-opt
python3 tools/optimize_images.py --tree content   # gambar artikel hasil crawl
```

Aman dijalankan berulang. Varian yang sudah lebih baru dari sumbernya dilewati.

Berat gambar 18 halaman pada desktop 1440 piksel, memilih varian seperti
peramban: 131,8 MB menjadi 4,4 MB, 97 persen lebih kecil. Sebelumnya yang
terberat `/page/publication/latest-update` 24,8 MB dan
`/page/our-business/education` 20,7 MB. Keduanya sekarang di bawah 0,4 MB.

Slider galeri beranda tidak masuk angka itu karena `src`-nya ada di payload
Alpine, bukan atribut `<img src>`, sehingga pemindainya tidak melihatnya.
Terpisah, 5 slide turun dari 801 KB menjadi 159 KB. Variannya dipilih saat
render di `routes/web.php`, sementara kolom `slide_banners.image` tetap
menyimpan gambar master.

### Menulis gambar di view

Pakai `<x-img>`, bukan `<img>`:

```blade
<x-img src="/assets/img/pages/our-business-energy/content-1.jpg" alt="Energi"
       sizes="(min-width: 768px) 768px, 100vw" class="w-full h-auto" />
```

`sizes` menentukan varian mana yang diunduh peramban, jadi salah menulisnya
berarti mengambil berkas yang lebih besar dari perlunya. Atribut lain diteruskan
apa adanya ke `<img>`.

### Celah yang perlu diketahui

Gambar sumber artikel (`content-migration/images/`, 139 MB) tidak dilacak git,
dan situs lama akan dimatikan saat cutover sehingga crawl ulang bukan jalan
keluar. Akibatnya atribut `src` pada `<img>` di halaman artikel menunjuk berkas
yang tidak ada di produksi. Peramban yang mendukung WebP tetap menampilkan
gambar dari `<source>`, sekitar 98 persen pengunjung, tetapi sisanya tidak
mendapat gambar artikel sama sekali. Menutupnya butuh varian JPEG kecil sebagai
cadangan, dan itu keputusan tersendiri.

## Aset frontend

Tailwind dan Alpine dibangun dan dilayani dari domain sendiri, termasuk
hurufnya. Sebelumnya `<head>` memuat `cdn.tailwindcss.com` (407 KB, membangun
CSS di dalam peramban) dan Alpine dari jsDelivr (56 KB), ditambah Google Fonts.
Saat pengujian kedua CDN pernah gagal bersamaan dan halaman kehilangan seluruh
gaya sekaligus semua interaksi.

| | Sebelum | Sesudah |
|---|---|---|
| Aset pihak ketiga | 3 host | tidak ada |
| CSS dan JS | 452 KB, CSS dibangun di peramban | 124 KB, sudah jadi |
| Total halaman tanpa gambar | | 226 KB |

Warna dan huruf yang dulu ditulis sebagai `tailwind.config` inline sekarang ada
di `@theme` pada `resources/css/app.css`.

### Kelas yang namanya disusun saat render

Play CDN membangun CSS dari kelas yang benar-benar ada di DOM, sedangkan build
memindai berkas sumber. Kartu SBU memakai `xl:col-span-{{ ... }}`, yang tidak
pernah terlihat utuh oleh pemindai, jadi kelasnya harus disebut lewat
`@source inline(...)` di `app.css`.

```sh
python3 tools/check_css_coverage.py
```

Sapuan ini mengumpulkan tiap nama kelas dari HTML seluruh halaman yang
dirender, termasuk atribut `x-transition` yang kelasnya ditambahkan Alpine saat
berjalan, lalu memastikan semuanya punya aturan di stylesheet yang dimuat. Ini
yang menemukan bahwa `prose-hasnur` tidak pernah punya satu aturan pun sejak
halaman artikel dibuat, sehingga daftar, tautan, dan gambar di badan artikel
tampil tanpa gaya. Jalankan setiap kali menambah kelas yang disusun dari
variabel.

Prototipe statis di akar repo masih memakai Play CDN. Itu rujukan desain, bukan
situs yang dikirim.

## Sebelum cutover

```sh
cd backend && php artisan serve --port=8123 &
python3 tools/qa_check.py --base http://127.0.0.1:8123
```

Sapuan ini memeriksa seluruh URL di sitemap (status, `<title>`, meta
description, canonical yang menunjuk dirinya sendiri, tepat satu `<h1>`, gambar
tanpa `alt`, duplikat title dan description), memastikan setiap tautan internal
bisa dibuka, dan menguji 61 URL artikel situs lama, apakah menjawab 200 atau
dialihkan, bukan 404. Keluar dengan status 1 bila ada temuan.

`/sitemap.xml` dilayani Laravel dari `config/site.php` dan tabel `posts`,
sehingga tidak bisa tertinggal dari isi situs. Berkas `sitemap.xml` di akar repo
hanya milik prototipe statis.

### URL lama yang artikelnya sudah dihapus

26 dari 61 artikel dihapus permanen karena tidak punya tanggal terbit, tetapi
URL-nya masih hidup di indeks Google. `App\Support\RetiredPosts` menjawabnya
410 Gone, bukan 404 dan bukan pengalihan. 410 berarti URL-nya memang sudah
tidak ada dan dikeluarkan dari indeks lebih cepat, sementara 404 dianggap
mungkin sementara dan pengalihan ke daftar kategori dinilai soft 404. Halamannya
di `resources/views/errors/410.blade.php` menjelaskan keadaan dan menawarkan
daftar kategorinya, kecuali daftar itu kosong.

`php artisan import:articles` melewati artikel tanpa tanggal supaya impor
berikutnya tidak menghidupkan mereka lagi diam-diam. Berkas crawl-nya tetap
disimpan, jadi `--with-undated` cukup untuk membatalkan keputusan itu.

Efek sampingnya, `she-activity` kehilangan seluruh 8 artikelnya. Halamannya
tetap bisa dibuka dengan keterangan "Belum ada publikasi", tetapi tidak diajukan
ke sitemap sampai ada isinya.

## Formulir kontak

Formulir di `/page/contact/contact-us` adalah satu-satunya jalur menuju
`info@hasnurgroup.com`. Alamat itu tidak terbit di mana pun dalam HTML, tidak di
footer dan tidak di JSON-LD, karena alamat yang terpampang adalah yang pertama
dipanen pengirim spam. JSON-LD memakai `contactPoint` yang menunjuk halaman
formulirnya, jadi sinyal kontak untuk Google tetap ada. Tujuannya disetel lewat
`MAIL_CONTACT_RECIPIENT` di `.env`.

Setiap kiriman disimpan lebih dulu, baru dikirim sebagai surel. Pengiriman surel
bisa gagal karena hal di luar kendali situs: kredensial kedaluwarsa, kuota habis,
penyedia menolak. Kalau pesannya hanya lewat surel, kegagalan seperti itu membuat
pertanyaan calon mitra hilang tanpa jejak. Kegagalan dicatat ke log, dan
pengunjung tetap mendapat konfirmasi karena dia memang sudah melakukan bagiannya.
Pesannya dibaca di panel pada menu Publikasi, Pesan Masuk, lengkap dengan lencana
jumlah yang belum ditangani.

Penyaringnya dua: medan perangkap yang tersembunyi dari mata dan pembaca layar,
serta batas 5 kiriman per 10 menit per alamat IP.

Satu `mailto:` masih tersisa dengan sengaja, `recruitment@hasnurgroup.com` di
halaman Lowongan Kami. Alamat itu berbeda dan memang dipakai untuk menerima
lamaran beserta lampirannya.

## Dari mana isi halaman datang

| Isi | Sumber | Cara mengubah |
|---|---|---|
| Artikel dan liputan media | tabel `posts` | `php artisan import:articles`, `php artisan news:add` |
| 7 kartu SBU di beranda | tabel `business_units` | `php artisan db:seed --class=BusinessUnitSeeder` |
| Slider galeri beranda | tabel `slide_banners` | `SlideBannerSeeder` |
| Alamat kantor di footer | tabel `offices` | `OfficeSeeder` |
| Lowongan kerja | tabel `vacancies` | belum ada isinya |
| Halaman rinci tiap SBU, Ikhtisar, CSR, SHE | view Blade | sunting berkasnya |

```sh
cd backend && php artisan migrate && php artisan db:seed
```

Seeder memakai `updateOrCreate` sehingga aman dijalankan berulang. Isinya
disalin apa adanya dari markup yang dulu ditulis tangan, diekstrak dengan
skrip dan bukan diketik ulang.

Lowongan sengaja kosong. Situs lama tidak pernah memuat satu lowongan pun, hanya
alamat surel rekrutmen. Halaman `/page/career/our-vacancy` sudah membangun
daftarnya dan menyatakan keadaannya saat kosong, jadi lowongan yang diisi dari
panel nanti langsung tampil tanpa menyentuh Blade.

Yang belum pindah ke basis data adalah isi panjang tiap halaman SBU. Tata
letaknya digarap satu per satu, dengan beberapa gambar di posisi tertentu di
antara paragraf, dan meratakannya menjadi satu blok teks akan menghilangkan
bentuk itu.

## Catatan operasional Docker

Image `php:8.4-fpm-alpine` berisi nginx dan supervisor dalam satu container,
ditambah service `penjadwal` yang menjalankan `schedule:work` sehingga
pemangkasan pesan berjalan tanpa cron. Aset dibangun di dalam image oleh tahap
Node, bukan disalin dari mesin pengembang. Dependensi PHP dipasang tanpa paket
dev dengan autoloader dioptimalkan.

Basis data, gambar unggahan, dan log disimpan sebagai volume sehingga bertahan
setiap kali image dibangun ulang.

Ukuran image 1,05 GB, sebagian besar dari 307 MB gambar situs. Yang 139 MB di
antaranya ada di `.gitignore`, jadi image yang dibangun dari klon baru tidak
memuatnya.

nginx sengaja sekontainer dengan PHP. Memisahkannya menjadi dua service terlihat
lebih rapi, tetapi nginx harus membaca berkas yang sama dengan PHP untuk
melayani gambar sehingga 165 MB aset ikut tersalin ke image kedua, dan berbagi
lewat named volume membuat aset beku pada isi pertama.

Volume `unggahan` juga menyimpan gambar hasil crawl. Docker menyalinnya dari
image saat volume pertama kali dibuat, dan sesudah itu isi volume yang menang.
Gambar baru yang ditambahkan ke repositori karena itu tidak muncul sampai
volumenya dibuat ulang dengan `docker compose down -v`, yang juga menghapus
basis data.

Backup berjalan di dalam Docker, dijadwalkan container `penjadwal` tiap 02.15
WIB. Hasilnya masuk volume `cadangan`, sengaja terpisah dari volume
`basis-data`: backup yang berada di volume yang sama dengan basis datanya akan
ikut hilang tepat pada kejadian yang membuatnya dibutuhkan.

Yang masih kurang: salinan di luar mesin. Backup hari ini tetap berada di host
yang sama dengan situsnya, jadi ia melindungi dari basis data yang rusak dan
penghapusan tak sengaja, tetapi tidak dari host yang hilang.

## Jebakan yang sudah ditemukan

Empat hal di bawah ini pernah memakan waktu, dan semuanya tidak meninggalkan
galat di log aplikasi.

**Aset yang dilayani aplikasi tertolak nginx.** Aturan cache untuk berkas
berakhiran `.js` dan `.css` sempat ditutup dengan `try_files $uri =404`.
Livewire menyajikan `/livewire/livewire.min.js` lewat rute, bukan berkas di
disk, sehingga nginx menolaknya sebelum PHP sempat menjawab. Livewire tidak
pernah hidup di peramban dan formulir login panel berubah menjadi kiriman GET
biasa yang hanya memuat ulang halaman. Blok statis sekarang jatuh ke `@laravel`.

**Berkas bermode 0600 menjawab 403.** Halamannya tetap tampil, hanya gambarnya
yang hilang, karena worker nginx bukan pemilik berkasnya. Dua gambar nilai inti
sempat begitu sejak diunduh. Image membetulkannya saat dibangun, tetapi deploy
rsync tidak, jadi mode berkas aset perlu diperiksa di repositori.

**Skema http di balik proxy.** Di balik terowongan atau reverse proxy, TLS
berhenti di tepi dan container menerima HTTP biasa. Tanpa daftar proxy tepercaya
di `backend/bootstrap/app.php`, Laravel membangun seluruh tautan dengan skema
`http://` meski halamannya dilayani lewat `https://`, dan peramban memblokirnya
sebagai mixed content.

**Cermin Composer yang tertinggal.** Di jaringan tempat ini dikerjakan, IP
`repo.packagist.org` diblokir pada porta 443 dan 80, sementara DNS-nya normal
dan GitHub tetap bisa. Jalan keluarnya memakai cermin, disetel global supaya
`composer.json` proyek tetap bersih:

```sh
composer config -g repo.packagist false
composer config -g repo.tencent composer https://mirrors.cloud.tencent.com/composer/
```

Pilih cerminnya dengan hati-hati. Cermin Aliyun yang sempat dipakai ternyata
tertinggal berbulan-bulan, di sana Laravel terbaru masih v12.40 dan Filament
v4.2, sehingga `composer require` menolak dengan alasan Laravel 13 tidak
didukung. Itu keliru, Filament v4.13 mendukungnya. Sebelum percaya pada pesan
konflik versi, periksa dulu apakah cerminnya mutakhir:

```sh
curl -s https://mirrors.cloud.tencent.com/composer/p2/laravel/framework.json \
  | python3 -c "import json,sys; print(json.load(sys.stdin)['packages']['laravel/framework'][0]['version'])"
```
