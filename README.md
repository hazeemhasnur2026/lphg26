# Fix Prototype - LP HGI

Situs korporat PT Hasnur Group. Aplikasi Laravel 13 dengan panel admin Filament,
tiga bahasa, dan pipeline gambar WebP. Prototipe statis yang jadi rujukan desain
masih ada di akar repo.

## Kebutuhan

| | Versi | Untuk |
|---|---|---|
| PHP | 8.4 ke atas | aplikasi. Bukan 8.3, lihat bagian Deploy |
| Composer | 2.x | dependensi PHP |
| Node | 22 ke atas | membangun CSS dan JS |
| SQLite atau MySQL | | database |
| `cwebp` dan `sips` | | hanya untuk mengonversi gambar baru |

`sips` bawaan macOS, jadi konverter gambarnya belum bisa jalan di Linux. Itu
tidak menghalangi deploy karena variannya sudah dilacak git.

## Struktur repo

```
backend/            aplikasi Laravel: situs publik dan panel admin
  app/Support/      pembaca manifest gambar, JSON-LD, daftar URL pensiun
  app/Filament/     panel admin, satu resource per model
  resources/views/  Blade, komponen x-img dan x-page-hero
assets/             gambar dan CSS bersama, dipakai aplikasi dan prototipe
  img-opt/          varian WebP hasil konversi, dilacak git
content-migration/  hasil crawl situs lama, gambar aslinya tidak dilacak
data/               manifest gambar dan daftar URL hasil crawl
tools/              konverter gambar, sapuan QA, pemeriksa cakupan CSS
scripts/            crawler artikel situs lama
deploy/             langkah yang dijalankan di server setelah deploy
docs/               spesifikasi, brief konten, laporan, audit kepatuhan
arsip/              bahan rujukan desain, tidak dirujuk kode mana pun
index.html, page/   prototipe statis, rujukan desain
robots.txt, sitemap.xml   milik prototipe statis; aplikasi menyajikan miliknya sendiri
```

Prototipe statis sengaja dibiarkan di akar. Ke-257 rujukan asetnya bersifat
relatif terhadap letak berkasnya, jadi memindahkannya ke subfolder menuntut
penulisan ulang seluruh path di 21 berkas HTML, sementara `assets/` sendiri tidak
bisa ikut pindah karena dilayani aplikasi lewat symlink. Risikonya tidak sebanding
dengan kerapian yang didapat.

## Menjalankan dengan Docker

Cara paling singkat untuk menjalankan seluruh aplikasi tanpa memasang PHP, Node,
maupun basis data di mesin sendiri.

```sh
cp deploy/env.production.example backend/.env
docker compose run --rm app php artisan key:generate --show   # salin hasilnya ke APP_KEY
docker compose up -d --build
```

Situs ada di `http://localhost:8080`, panel admin di `/admin`. Ganti porta lewat
`PORT_SITUS` di lingkungan.

Pada penyalaan pertama basis datanya masih kosong, jadi container mengisinya
sendiri: seeder untuk unit usaha, kantor, dan banner, lalu `import:articles`
untuk 35 artikel hasil crawl. Penyalaan berikutnya melewati langkah ini karena
berkas basis datanya sudah ada di volume.

Akun pertama dibuat dengan:

```sh
docker compose exec app php artisan make:filament-user
```

### Apa yang ada di dalamnya

| Bagian | Keterangan |
|---|---|
| Image | `php:8.4-fpm-alpine`, ditambah nginx dan supervisor, 1,05 GB |
| Aset | dibangun di dalam image oleh tahap Node, bukan disalin dari mesin pengembang |
| Dependensi PHP | tanpa paket dev, autoloader dioptimalkan |
| Service `app` | nginx dan php-fpm dalam satu container |
| Service `penjadwal` | `schedule:work`, menjalankan pemangkasan pesan tanpa perlu cron |

Basis data, gambar unggahan, dan log disimpan sebagai volume, jadi isinya bertahan
setiap kali image dibangun ulang.

Ukuran image sebagian besar berasal dari 307 MB gambar situs: 141 MB di `assets/img`
dan 139 MB gambar asli hasil crawl.

Yang 139 MB itu ada di `.gitignore`. Git hanya melacak 249 varian WebP-nya, karena
gambar aslinya berasal dari situs lama yang akan dimatikan. Jadi image yang dibangun
di mesin tempat crawl dijalankan memuat gambar asli itu, sedangkan image yang
dibangun dari klon baru tidak. Peramban yang mendukung WebP tetap mendapat gambarnya
dari `<source>`; yang tidak mendukung mendapat `<img src>` yang kosong. Menutupnya
butuh berkas JPEG kecil sebagai cadangan, dan itu keputusan tersendiri.

### Kenapa nginx sekontainer dengan PHP

Memisahkannya menjadi dua service terlihat lebih rapi, tetapi pada aplikasi ini
menimbulkan dua masalah. nginx harus membaca berkas yang sama dengan PHP untuk
melayani gambar, sehingga 165 MB aset ikut tersalin ke image kedua. Dan berbagi
lewat named volume membuat aset beku pada isi pertama: membangun ulang image tidak
memperbaruinya, jadi nginx menyajikan aset lama tanpa ada yang menyadarinya.

### Yang tetap harus dipikirkan saat deploy

Volume `unggahan` memuat gambar yang diunggah dari panel admin dan tidak ada di
repositori mana pun. Pada penyedia dengan filesystem ephemeral, isinya hilang tiap
deploy kecuali volume itu benar-benar dipasang.

Volume yang sama juga menyimpan gambar hasil crawl. Docker menyalinnya dari image
saat volume pertama kali dibuat, dan sesudah itu isi volume yang menang. Gambar baru
yang ditambahkan ke repositori karena itu tidak muncul sampai volumenya dibuat ulang
dengan `docker compose down -v`, yang juga menghapus basis data.

Berkas dengan mode 0600 menjawab 403 lewat nginx meski halamannya tetap tampil,
karena worker nginx bukan pemilik berkasnya. Dua gambar nilai inti sempat begitu
sejak diunduh. Image membetulkannya sendiri saat dibangun, tetapi deploy rsync tidak,
jadi mode berkas aset perlu diperiksa di repositori.

Backup tidak berjalan di dalam Docker. `deploy/backup.sh` dijalankan dari host,
mengarah ke volume yang sama.

## Menyiapkan aplikasi

```sh
cd backend
composer install
php artisan migrate --seed
npm ci && npm run build      # wajib: tanpa ini halaman error
php artisan serve
```

`npm run build` bukan opsional. Tailwind dan Alpine tidak lagi diambil dari CDN
dan `public/build/` tidak dilacak git, jadi `@vite` akan melempar error sampai
hasil buildnya ada. Rangkaian test tidak terpengaruh. `TestCase` memakai
`withoutVite()`.

## Admin CMS (Filament)

Panel ada di `/admin`. Buat user pertama dengan:

```sh
php artisan make:filament-user
```

| Menu | Mengelola | Terlihat di |
|---|---|---|
| Publikasi → Update Terbaru / Berita / Press Release | `posts` | daftar publikasi, halaman artikel, kartu beranda, footer, sitemap |
| CSR → Aktifitas CSR | `posts` | `/page/csr/csr-activity` |
| SHE → Aktivitas SHE | `posts` | `/page/she/she-activity` |
| Konten Situs → Unit Bisnis | `business_units` | 7 kartu SBU di beranda |
| Konten Situs → Slider Beranda | `slide_banners` | slider galeri beranda |
| Konten Situs → Kantor | `offices` | alamat di footer |
| Karir → Lowongan | `vacancies` | halaman Lowongan Kami |

Kelima kategori artikel punya menunya sendiri, mengikuti bentuk navigasi situs
publik. Membuka satu menu berarti melihat daftar kategori itu saja, dan tombol
Buat di situ langsung membuat artikel dengan kategori yang sama. Kategorinya
tetap bisa diubah dari dalam form.

Ada dua peran. **Administrator** membuka seluruh menu termasuk Pesan Masuk dan
Pengguna; **Editor Konten** hanya artikel, unit bisnis, slider, kantor, dan lowongan.
Akun baru bawaannya Editor. Peran diatur dari menu Pengaturan, Pengguna.

Dua baris cron yang harus dipasang di server:

```
* * * * *   cd /path/ke/backend && php artisan schedule:run >> /dev/null 2>&1
15 2 * * *  /path/ke/deploy/backup.sh >> /var/log/hasnur-backup.log 2>&1
```

Yang pertama menjalankan pemangkasan pesan yang melewati masa simpan
(`CONTACT_RETENTION_MONTHS`, bawaan 24 bulan). Yang kedua mencadangkan basis data dan
gambar unggahan setiap hari, disimpan 30 hari. Tanpa keduanya, retensi dan backup
tidak pernah berjalan dan tidak ada yang memberitahu.

Prosedur bila terjadi kebocoran data ada di `docs/prosedur-kebocoran-data.md`.

Sebelumnya setiap akun di tabel `users` adalah pengelola penuh, belum ada sistem peran.
Tempat menambahkannya nanti ada di `User::canAccessPanel()`. Tanpa metode itu
Filament hanya mengizinkan akses saat `APP_ENV=local`, jadi di produksi tidak
ada satu pun akun yang bisa masuk.

**Gambar yang diunggah belum otomatis jadi WebP.** Unggahan mendarat di disk
yang sama dengan gambar lama (`assets` → `assets/img/`, `content` →
`content-migration/images/`), jadi cukup jalankan konverternya setelah
mengunggah dan variannya ikut terbentuk. Sebelum itu dijalankan, gambar baru
tetap tampil, hanya memakai berkas aslinya yang lebih berat.

### Halaman rinci SBU belum bisa disunting dari admin

Isi panjang tiap halaman unit bisnis masih view Blade; kolom `full_content`
sengaja tidak dimunculkan di form supaya tidak terlihat seolah bisa dipakai.
Alasannya ada di bagian "Dari mana isi halaman datang".

### Kalau `composer` tidak bisa menjangkau packagist

Di jaringan tempat ini dikerjakan, IP `repo.packagist.org` diblokir. Port 443
dan 80 dua-duanya, sementara DNS-nya normal dan GitHub tetap bisa. Jalan
keluarnya memakai cermin Composer, disetel **global** supaya `composer.json`
proyek tetap bersih:

```sh
composer config -g repo.packagist false
composer config -g repo.tencent composer https://mirrors.cloud.tencent.com/composer/
```

Pilih cerminnya dengan hati-hati. Cermin Aliyun yang sempat dipakai ternyata
ketinggalan berbulan-bulan, di sana Laravel terbaru masih v12.40 dan Filament
v4.2, sehingga `composer require` menolak dengan alasan Laravel 13 tidak
didukung. Itu keliru: Filament v4.13 mendukungnya. Sebelum percaya pada pesan
konflik versi, periksa dulu apakah cerminnya mutakhir:

```sh
curl -s https://mirrors.cloud.tencent.com/composer/p2/laravel/framework.json \
  | python3 -c "import json,sys; print(json.load(sys.stdin)['packages']['laravel/framework'][0]['version'])"
```

## Deploy

Tiap push ke `main` menjalankan **CI** (`.github/workflows/ci.yml`): 58 test,
Pint, dan build aset pada PHP 8.4. **Deploy** (`deploy.yml`) menyusul hanya bila
CI lulus, mengirim berkas lewat rsync/SSH lalu menjalankan
`deploy/after-deploy.sh` di server.

**Butuh PHP 8.4 ke atas.** Bukan 8.3: Laravel 13 membawa Symfony 8, dan bersama
plugin terjemahan Filament ada 18 paket terkunci yang menuntut 8.4. Hosting PHP
8.3 akan gagal saat `composer install`.

### Secrets yang harus diisi

Settings → Secrets and variables → Actions:

| Secret | Isi |
|---|---|
| `DEPLOY_HOST` | alamat server |
| `DEPLOY_USER` | pengguna SSH |
| `DEPLOY_PATH` | folder tujuan di server, isi repo ditaruh langsung di sini |
| `DEPLOY_SSH_KEY` | kunci privat SSH, seluruhnya termasuk baris BEGIN/END |
| `DEPLOY_PORT` | opsional, default 22 |

Tanpa `DEPLOY_HOST`, workflow deploy berhenti dengan keterangan dan tidak
mencoba apa-apa. Tidak ada kredensial yang boleh masuk repo ini, sekalipun repositorinya
privat: riwayat git tersimpan permanen dan hak akses bisa berubah.

### Yang harus disiapkan sekali di server

1. `backend/.env`, tidak pernah ikut terkirim. Salin dari
   `deploy/env.production.example`, yang sudah berisi nilai produksi yang benar,
   lalu isi `APP_KEY` (`php artisan key:generate`), `APP_URL`, dan setelan SMTP.
   `deploy/after-deploy.sh` menolak melanjutkan bila `APP_ENV`, `APP_DEBUG`,
   `SESSION_SECURE_COOKIE`, atau `APP_KEY` tidak sesuai.
2. Arahkan document root web server ke `backend/public`.
3. Database: SQLite cukup untuk trafik situs ini. Pastikan berkasnya di luar
   document root dan bisa ditulis. MySQL juga didukung, tinggal ganti `DB_*`.

`deploy/after-deploy.sh` mengurus sisanya tiap deploy: membuat ulang tiga
symlink di `backend/public/` yang tidak dilacak git. Tanpa itu **seluruh gambar
situs menjawab 404 sementara halamannya tetap tampil**, jadi mudah lolos dari
pemeriksaan sekilas, lalu menyetel izin tulis, menjalankan migrasi, dan
menyalakan cache config, rute, view, dan event.

Yang **tidak** ikut terhapus saat rsync: `.env`, berkas SQLite, folder unggahan,
dan gambar artikel hasil crawl. Ketiganya hidup di server dan tidak ada di repo.

## Gambar: varian WebP

Gambar asli di repo ini beresolusi kamera. Beberapa 5000x3750 piksel, dua di
antaranya lebih dari 5 MB. Yang dikirim ke browser bukan berkas itu, melainkan
varian WebP yang sudah ada di repo. Konverter hanya perlu dijalankan lagi setelah
**menambah atau mengganti** gambar sumber:

```sh
python3 tools/optimize_images.py --tree assets    # assets/img  -> assets/img-opt
python3 tools/optimize_images.py --tree content   # gambar artikel hasil crawl
```

Butuh `cwebp` (`brew install webp`) dan `sips`. `sips` hanya ada di macOS, jadi
konverternya belum bisa jalan di Linux, bukan masalah selama variannya dilacak
git, tapi perlu diganti kalau suatu saat mau dibuat saat deploy. Aman dijalankan
berulang: varian yang sudah lebih baru dari sumbernya dilewat.

Hasilnya **dilacak git** (628 berkas, 45 MB) bersama manifestnya,
`data/image-variants.json`. Itulah berkas yang benar-benar dikirim ke browser,
jadi clone dan deploy langsung menyajikan gambar yang sudah dioptimalkan tanpa
menjalankan apa pun.

Yang 18 MB untuk gambar artikel memang tidak bisa dibuat ulang dari repo:
gambar sumbernya (`content-migration/images/`, 139 MB resolusi kamera) tidak
dilacak, dan situs lama akan dimatikan saat cutover, jadi crawl ulang bukan
jalan keluar.

**Celah yang perlu diketahui:** karena sumber artikel tidak dilacak, atribut
`src` pada `<img>` di halaman artikel menunjuk berkas yang tidak ada di produksi.
Browser yang mendukung WebP tetap menampilkan gambar dari `<source>`, itu
sekitar 98% pengunjung, tapi sisanya tidak mendapat gambar artikel sama sekali.
Menutupnya butuh varian JPEG kecil sebagai cadangan, dan itu keputusan tersendiri.

### Angka

Berat gambar 18 halaman, desktop 1440 px, memilih varian seperti browser:
**131,8 MB -> 4,4 MB (97% lebih kecil)**. Yang terburuk sebelumnya
`/page/publication/latest-update` 24,8 MB dan `/page/our-business/education`
20,7 MB; keduanya sekarang di bawah 0,4 MB.

Slider galeri beranda tidak ikut angka itu. `src`-nya ada di payload Alpine,
bukan atribut `<img src>`, jadi pemindainya tidak melihatnya. Terpisah: 5 slide
**801 KB -> 159 KB**. Varian dipilih saat render (`routes/web.php`), sementara
kolom `slide_banners.image` tetap menyimpan gambar master.

## Menulis gambar di view

Pakai `<x-img>`, bukan `<img>`:

```blade
<x-img src="/assets/img/pages/our-business-energy/content-1.jpg" alt="Energi"
       sizes="(min-width: 768px) 768px, 100vw" class="w-full h-auto" />
```

`sizes` wajib diperhatikan, itu yang menentukan varian mana yang diunduh.
Salah tulis berarti browser mengambil berkas yang lebih besar dari perlunya.
Atribut lain diteruskan apa adanya ke `<img>`.

## Sebelum cutover

```sh
cd backend && php artisan serve --port=8123 &
python3 tools/qa_check.py --base http://127.0.0.1:8123
```

Sapuan ini memeriksa seluruh URL di sitemap (status, `<title>`, meta
description, canonical yang menunjuk dirinya sendiri, tepat satu `<h1>`, gambar
tanpa `alt`, duplikat title/description), memastikan setiap tautan internal bisa
dibuka, dan menguji **61 URL artikel situs lama**, apakah balas 200 atau
dialihkan, bukan 404. Keluar dengan status 1 bila ada temuan.

`/sitemap.xml` dilayani Laravel dari `config/site.php` + tabel `posts`, jadi
tidak bisa tertinggal dari isi situs. Berkas `sitemap.xml` di root repo hanya
milik prototipe statis dan tidak dipakai aplikasi.

### URL lama yang artikelnya sudah dihapus

26 dari 61 artikel dihapus permanen karena tidak punya tanggal terbit, tapi
URL-nya masih hidup di indeks Google. `App\Support\RetiredPosts` menjawabnya
**410 Gone**, bukan 404 dan bukan pengalihan: 410 berarti URL-nya memang sudah
tidak ada dan dikeluarkan dari indeks lebih cepat, sementara 404 dianggap
mungkin sementara dan pengalihan ke daftar kategori dinilai soft 404.
Halamannya (`resources/views/errors/410.blade.php`) menjelaskan keadaan dalam
bahasa Indonesia dan menawarkan daftar kategorinya, kecuali daftar itu kosong.

`php artisan import:articles` melewati artikel tanpa tanggal, supaya impor
berikutnya tidak menghidupkan mereka lagi diam-diam. Berkas crawl-nya tetap
disimpan, jadi `--with-undated` cukup untuk membatalkan keputusan itu.

Efek sampingnya: `she-activity` kehilangan seluruh 8 artikelnya. Halamannya
tetap bisa dibuka dengan keterangan "Belum ada publikasi", tapi tidak diajukan
ke sitemap sampai ada isinya.

## Hubungi Kami

Formulir di `/page/contact/contact-us` adalah satu-satunya jalur menuju
`info@hasnurgroup.com`. Alamat itu **tidak terbit di mana pun** dalam HTML,
tidak di footer, tidak di JSON-LD, karena alamat yang terpampang adalah yang
pertama dipanen pengirim spam. JSON-LD memakai `contactPoint` yang menunjuk
halaman formulirnya, jadi sinyal kontak untuk Google tetap ada.

Tujuannya disetel lewat `MAIL_CONTACT_RECIPIENT` di `.env`.

Setiap kiriman **disimpan lebih dulu**, baru dikirim sebagai surel. Pengiriman
surel bisa gagal karena hal di luar kendali situs. Kredensial kedaluwarsa,
kuota habis, penyedia menolak, dan kalau pesannya hanya lewat surel, kegagalan
seperti itu membuat pertanyaan calon mitra hilang tanpa jejak. Kegagalan dicatat
ke log, dan pengunjung tetap mendapat konfirmasi karena dia memang sudah
melakukan bagiannya. Pesannya dibaca di CMS pada menu **Publikasi → Pesan Masuk**,
lengkap dengan lencana jumlah yang belum ditangani.

Penyaringnya: medan perangkap yang tersembunyi dari mata dan pembaca layar, plus
batas 5 kiriman per 10 menit per alamat IP.

Satu `mailto:` masih tersisa dengan sengaja. `recruitment@hasnurgroup.com` di
halaman Lowongan Kami, alamat yang berbeda dan memang dipakai untuk menerima
lamaran beserta lampirannya.

## Dari mana isi halaman datang

| Isi | Sumber | Cara mengubah |
|---|---|---|
| Artikel & liputan media | tabel `posts` | `php artisan import:articles`, `php artisan news:add` |
| 7 kartu SBU di beranda | tabel `business_units` | `php artisan db:seed --class=BusinessUnitSeeder` |
| Slider galeri beranda | tabel `slide_banners` | `SlideBannerSeeder` |
| Alamat kantor di footer | tabel `offices` | `OfficeSeeder` |
| Lowongan kerja | tabel `vacancies` | belum ada isinya. Lihat di bawah |
| Halaman rinci tiap SBU, Ikhtisar, CSR, SHE | view Blade | sunting berkasnya |

```sh
cd backend && php artisan migrate && php artisan db:seed
```

Seeder memakai `updateOrCreate`, jadi aman dijalankan berulang. Isinya disalin
apa adanya dari markup yang dulu ditulis tangan. Diekstrak dengan skrip, bukan
diketik ulang.

**Lowongan sengaja kosong.** Situs lama tidak pernah memuat satu lowongan pun,
hanya alamat email rekrutmen. Halaman `/page/career/our-vacancy` sudah membangun
daftarnya dan menyatakan keadaannya saat kosong, jadi lowongan yang diisi dari
admin nanti langsung tampil tanpa menyentuh Blade.

**Yang belum pindah ke database:** isi panjang tiap halaman SBU
(`full_content` masih kosong). Tata letaknya digarap satu per satu. Beberapa
gambar di posisi tertentu di antara paragraf, dan meratakannya jadi satu blok
teks akan menghilangkan bentuk itu. Perlu keputusan tersendiri, bukan pekerjaan
mekanis.

## Tailwind & Alpine: dibangun, bukan dari CDN

Sebelumnya `<head>` memuat `cdn.tailwindcss.com` (407 KB, membangun CSS di dalam
browser) dan Alpine dari jsDelivr (56 KB), ditambah Google Fonts. Saat pengujian
kedua CDN pernah gagal bersamaan dan halaman kehilangan seluruh gaya sekaligus
semua interaksi. Sekarang semuanya dibangun dan dilayani dari domain sendiri,
termasuk hurufnya:

| | Sebelum | Sesudah |
|---|---|---|
| Aset dari pihak ketiga | 3 host (Tailwind, jsDelivr, Google Fonts) | tidak ada |
| CSS + JS | 452 KB, CSS dibangun di browser | 124 KB, sudah jadi |
| Total halaman tanpa gambar |, | 226 KB |

Warna dan huruf yang dulu ditulis sebagai `tailwind.config` inline sekarang ada
di `@theme` pada `resources/css/app.css`.

### Kelas yang namanya disusun saat render

Ini perbedaan penting dari Play CDN: CDN membangun CSS dari kelas yang benar-benar
ada di DOM, sedangkan build memindai berkas sumber. Kartu SBU memakai
`xl:col-span-{{ ... }}`, yang tidak pernah terlihat utuh oleh pemindai, jadi
kelasnya harus disebut lewat `@source inline(...)` di `app.css`.

```sh
python3 tools/check_css_coverage.py
```

Sapuan ini mengumpulkan tiap nama kelas dari HTML seluruh halaman yang dirender,
termasuk atribut `x-transition` yang kelasnya ditambahkan Alpine saat berjalan,
lalu memastikan semuanya punya aturan di stylesheet yang dimuat. Ini yang
menemukan bahwa `prose-hasnur` tidak pernah punya satu aturan pun sejak halaman
artikel dibuat, sehingga daftar, tautan, dan gambar di badan artikel tampil tanpa
gaya. Jalankan setiap kali menambah kelas yang disusun dari variabel.

Prototipe statis di root repo masih memakai Play CDN. Itu memang rujukan desain,
bukan situs yang dikirim.
