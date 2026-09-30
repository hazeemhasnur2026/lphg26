# Panduan Deploy — Server Dev

Langkah deploy manual ke server dev pertama kali, sebelum jalur otomatis
(`deploy.yml`) disambungkan. Ditulis untuk target berikut:

| | |
|---|---|
| IP | `110.238.109.55` |
| Domain | `dev-web.hasnurgroup.com` |
| Path aplikasi | `/var/www/html/web-jg` |
| Document root nyata | `/var/www/html/web-jg/backend/public` |

Document root **bukan** `/var/www/html/web-jg`, melainkan `backend/public` di
dalamnya. Mengarahkan domain ke folder repo langsung akan membocorkan
`.env`, `composer.json`, dan seluruh isi `app/` ke publik.

---

## 0. Kenapa manual dulu, bukan lewat CI/CD

Proyek ini sudah punya jalur deploy otomatis (`.github/workflows/deploy.yml`):
tiap push ke `main` yang lulus CI dikirim lewat rsync/SSH ke satu tujuan yang
diset lewat GitHub Secrets (`DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PATH`,
`DEPLOY_SSH_KEY`). Tapi jalur itu:

- Hanya terpicu dari branch `main`, bukan branch kerja seperti
  `hazeemhasnur2026/shrimp`.
- Mengasumsikan server tujuan **sudah disiapkan** — punya PHP, nginx,
  `.env`, dan basis data. `deploy.yml` hanya mengirim berkas dan menjalankan
  `deploy/after-deploy.sh`, tidak menginstal apa pun dari nol.

Server dev ini belum pernah disentuh, jadi persiapan sekali di bawah wajib
dilakukan manual dulu. Setelah server siap, menyambungkan CI/CD tinggal isi
Secrets (lihat bagian 9).

---

## 1. Prasyarat di server

Cek dulu apa yang sudah ada:

```bash
php -v
nginx -v 2>/dev/null || apache2 -v 2>/dev/null
composer -V
node -v
```

Proyek ini mewajibkan **PHP 8.4 ke atas** (`backend/composer.json`). Laravel 13
membawa Symfony 8, dan 18 paket terkunci di antaranya menuntut 8.4 — server
dengan PHP 8.3 ke bawah akan gagal persis di `composer install`. Kalau versi
di server masih di bawah itu:

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-mbstring php8.4-xml \
  php8.4-sqlite3 php8.4-gd php8.4-intl php8.4-bcmath php8.4-curl php8.4-zip
```

Cek ekstensi GD punya dukungan WebP — fitur optimasi gambar CMS
(`app/Support/PengoptimalGambar.php`) bergantung penuh padanya, dan tanpa
dukungan WebP gambar yang diunggah dari admin tetap tersimpan tapi tidak
pernah dikonversi, gagal secara diam-diam:

```bash
php -r "var_dump(function_exists('imagewebp'));"
```

Kalau `false`, pasang `libwebp-dev` sebelum memasang ulang `php8.4-gd`.

Node 22 ke atas untuk membangun CSS/JS (Tailwind, Alpine):

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo bash -
sudo apt install -y nodejs
```

## 2. DNS

Pastikan A record `dev-web.hasnurgroup.com` sudah menunjuk ke
`110.238.109.55` sebelum lanjut ke SSL (bagian 8):

```bash
dig dev-web.hasnurgroup.com +short
```

## 3. Ambil kode ke server

```bash
sudo mkdir -p /var/www/html/web-jg
sudo chown $USER:$USER /var/www/html/web-jg
git clone --branch hazeemhasnur2026/shrimp <url-remote-repo> /var/www/html/web-jg
cd /var/www/html/web-jg
```

## 4. Dependensi & build aset

```bash
cd /var/www/html/web-jg/backend
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
```

`--no-dev`: paket pengembangan (Pint, PHPUnit) tidak punya urusan di server,
dan ikut memasangnya hanya memperbesar permukaan yang bisa diserang.

## 5. Symlink wajib

```bash
cd /var/www/html/web-jg
bash deploy/link-public-dirs.sh
```

Skrip ini membuat tiga symlink di `backend/public/`: `assets`,
`content-images`, `content-images-opt`. Isinya berada di luar `backend/`,
sengaja tidak dilacak git (lihat komentar di dalam skripnya), jadi tanpa
langkah ini seluruh gambar situs menjawab 404 sementara halamannya sendiri
tetap tampil normal — jenis kerusakan yang lolos dari pemeriksaan sekilas.

## 6. `.env`

```bash
cd /var/www/html/web-jg/backend
cp ../deploy/env.production.example .env
php artisan key:generate
touch database/database.sqlite
```

Edit `.env`, minimal:

```
APP_URL=https://dev-web.hasnurgroup.com
DB_DATABASE=/var/www/html/web-jg/backend/database/database.sqlite
```

`deploy/after-deploy.sh` (bagian 7) menolak melanjutkan bila `APP_ENV`,
`APP_DEBUG`, `SESSION_SECURE_COOKIE`, atau `APP_KEY` tidak sesuai nilai di
`deploy/env.production.example`. Ini disengaja: nilai yang salah di sini
tidak membuat situs gagal jalan — justru situs tetap tampil normal sambil
membocorkan jejak tumpukan dan isi variabel lingkungan ke siapa pun yang
memicu galat. Kalau server dev ini memang perlu `APP_DEBUG=true` untuk
memudahkan penelusuran bug, lewati skrip itu dan jalankan migrasi/cache
manual (bagian 7, opsi B) — tapi pastikan tidak lupa mematikannya lagi
sebelum server ini dipakai siapa pun selain tim developer.

## 7. Izin tulis

```bash
cd /var/www/html/web-jg/backend
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache database/database.sqlite
sudo chmod -R ug+rw storage bootstrap/cache
```

`www-data` adalah user default php-fpm di Ubuntu/Debian. Cek dengan
`ps aux | grep php-fpm` kalau servernya dikonfigurasi memakai user lain.

## 8. Migrasi & cache

**Opsi A — pakai skrip resmi** (kalau `.env` sudah ikut nilai template
produksi):

```bash
cd /var/www/html/web-jg
bash deploy/after-deploy.sh
```

**Opsi B — manual** (kalau `.env` dev-mu longgar, misalnya `APP_DEBUG=true`):

```bash
cd /var/www/html/web-jg/backend
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 9. Web server

Cek dulu yang aktif (`nginx -v` atau `apache2 -v` dari bagian 1).

### nginx

Buat `/etc/nginx/sites-available/dev-web.hasnurgroup.com`:

```nginx
server {
    listen 80;
    server_name dev-web.hasnurgroup.com;

    root /var/www/html/web-jg/backend/public;
    index index.php;

    client_max_body_size 12M;

    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~* \.(webp|jpg|jpeg|png|gif|svg|woff2?|css|js)$ {
        expires 1y;
        access_log off;
        add_header X-Content-Type-Options "nosniff" always;
        add_header X-Frame-Options "DENY" always;
        add_header Referrer-Policy "strict-origin-when-cross-origin" always;
        add_header Cache-Control "public, immutable" always;
        try_files $uri @laravel;
    }

    location ~ ^/(content-images|content-images-opt|assets)/.*\.(php|phtml|phar|phps)$ {
        deny all;
    }

    location @laravel {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_hide_header X-Powered-By;
    }

    location = /index.php {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\. {
        deny all;
    }
}
```

Ini adalah port bare-metal dari `docker/nginx.conf` di proyek ini, dengan dua
perbaikan keamanan yang pernah ditemukan lewat audit dan sengaja
dipertahankan di sini:

- **Blok eksekusi PHP di folder upload.** `location ~ \.php$` yang umum
  dipakai contoh nginx di internet mencocokkan berkas `.php` mana pun di
  bawah document root — termasuk folder `content-images/` yang isinya
  hasil unggahan orang. Diuji langsung sebelumnya: berkas `.php` yang
  ditaruh di sana benar-benar dieksekusi php-fpm. Konfigurasi ini
  mempersempit eksekusi PHP hanya untuk `index.php` (`location = /index.php`),
  dan mengeksplisitkan `deny all` untuk `.php`/`.phtml`/`.phar`/`.phps` di tiga
  folder upload.
- **Header keamanan diulang di blok aset statis.** `add_header` di level
  `server` tidak diwariskan nginx ke blok `location` yang punya `add_header`
  sendiri. Begitu blok aset statis menambahkan `Cache-Control`, tiga header
  di atasnya (termasuk `X-Content-Type-Options: nosniff`) berhenti terkirim
  untuk setiap gambar dan berkas CSS/JS — justru pada berkas unggahan, yang
  paling butuh header itu.

Path socket php-fpm (`unix:/run/php/php8.4-fpm.sock`) bisa berbeda tergantung
versi PHP yang benar-benar aktif. Cek dengan `ls /run/php/`.

Aktifkan:

```bash
sudo ln -s /etc/nginx/sites-available/dev-web.hasnurgroup.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache

Belum ditulis di sini — kalau server dev ini ternyata pakai Apache, bukan
nginx, konfigurasinya beda (`.htaccess` + `mod_rewrite` + proxy ke php-fpm).
Minta panduan terpisah sebelum lanjut, jangan asal salin konfigurasi nginx
di atas ke Apache.

## 10. SSL

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d dev-web.hasnurgroup.com
```

Perlu DNS (bagian 2) sudah aktif sebelum langkah ini, certbot memverifikasi
domain lewat HTTP.

## 11. Cron

Satu baris, bukan lebih:

```bash
crontab -e
```

```
* * * * * cd /var/www/html/web-jg/backend && php artisan schedule:run >> /dev/null 2>&1
```

Pekerjaan berkala dijadwalkan di dalam aplikasi
(`backend/routes/console.php`), bukan di baris cron terpisah-pisah: backup
harian, pemangkasan pesan kontak yang melewati masa simpan
(`CONTACT_RETENTION_MONTHS`), pemangkasan peristiwa analitik
(`ANALYTICS_RETENTION_MONTHS`), dan pemangkasan riwayat versi artikel
(`REVISION_RETENTION`). Semuanya ikut mati kalau baris cron ini tidak ada,
dan tidak ada yang memberi tahu — situs tetap tampil normal, hanya data lama
menumpuk selamanya.

## 12. Akun admin pertama

Proyek ini sengaja tidak punya seeder user (lihat `DatabaseSeeder.php`) —
mengarang akun admin dengan kata sandi bawaan di kode sumber adalah
kredensial produksi yang ikut ter-commit ke git selamanya. Buat manual:

```bash
cd /var/www/html/web-jg/backend
php artisan make:filament-user
```

Akun baru berperan `contributor` secara default. Naikkan ke `super_admin`:

```bash
php artisan tinker --execute="App\Models\User::first()->update(['role' => 'super_admin'])"
```

## 13. Verifikasi

```bash
curl -I https://dev-web.hasnurgroup.com
```

Buka di browser:

- `https://dev-web.hasnurgroup.com` — beranda publik
- `https://dev-web.hasnurgroup.com/admin` — panel CMS

---

## Menyambungkan CI/CD otomatis (opsional, setelah server ini stabil)

Setelah server dev ini berjalan dan dianggap siap menerima deploy otomatis
dari push git, isi Secrets di GitHub (**Settings → Secrets and variables →
Actions**):

| Secret | Isi |
|---|---|
| `DEPLOY_HOST` | `110.238.109.55` |
| `DEPLOY_USER` | user SSH yang dipakai deploy |
| `DEPLOY_PATH` | `/var/www/html/web-jg` |
| `DEPLOY_SSH_KEY` | kunci privat SSH, seluruhnya termasuk baris `BEGIN`/`END` |
| `DEPLOY_PORT` | opsional, bawaan 22 |

Catatan penting: `.github/workflows/deploy.yml` saat ini hanya terpicu dari
branch `main` dan konfigurasinya (`concurrency: deploy-production`) ditulis
untuk **satu** tujuan. Kalau server dev ini dimaksudkan tetap terpisah dari
server produksi nantinya (dua tujuan berbeda dari dua branch berbeda),
`deploy.yml` perlu disalin/disesuaikan dulu — jangan langsung isi Secrets
di atas kalau repo yang sama nantinya juga dipakai deploy ke server
produksi lain, karena Secrets ini akan menimpa tujuan yang sama untuk
keduanya.

---

## Troubleshooting singkat

| Gejala | Kemungkinan penyebab |
|---|---|
| Domain "can't be reached" | DNS belum aktif, atau firewall/security group Huawei Cloud belum membuka port 80/443 |
| `/admin` 404, beranda normal | Route cache belum dibangun ulang setelah update, atau salah document root untuk sub-path tertentu |
| Beranda tampil situs lama/statis | Document root salah arah — pastikan menunjuk ke `backend/public`, bukan ke `/var/www/html/web-jg` |
| Semua gambar 404, halaman tampil normal | Lupa jalankan `deploy/link-public-dirs.sh` (bagian 5) |
| Login admin gagal terus, tidak ada error jelas | `SESSION_SECURE_COOKIE=true` di `.env` tapi situs belum jalan lewat HTTPS — selesaikan SSL dulu (bagian 10), atau set `false` sementara khusus untuk tes lewat HTTP |
| Upload gambar sukses tapi tidak pernah muncul varian WebP | GD tanpa dukungan WebP — cek ulang bagian 1 |
