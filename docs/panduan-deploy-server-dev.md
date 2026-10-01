# Panduan Deploy — Server Dev (Docker)

Langkah deploy ke server dev pakai Docker Compose, mengikuti `compose.yaml` dan
`Dockerfile` yang sudah ada di proyek ini — bukan instalasi PHP/nginx langsung
di server (lihat riwayat: versi bare-metal sempat ditulis lebih dulu, lalu
diganti ke Docker).

| | |
|---|---|
| IP | `110.238.109.55` |
| Domain | `dev-web.hasnurgroup.com` |
| Path aplikasi | `/var/www/html/web-jg` |

---

## 0. Kenapa Docker di sini lebih sederhana

Semua yang di versi bare-metal harus disiapkan manual satu per satu — versi
PHP, ekstensi GD+WebP, nginx, symlink, migrasi, cache — sudah dibungkus
`Dockerfile` dan `docker/entrypoint.sh`. Server hanya perlu Docker terpasang;
sisanya otomatis tiap container menyala: symlink dibuat ulang, migrasi
dijalankan, dan pada nyala pertama basis data ikut diisi (unit bisnis, kantor,
banner, 61 artikel hasil impor).

Konsekuensinya: kalau ada yang gagal, penyebabnya nyaris selalu ada di tiga
tempat — `.env`, volume, atau port — bukan di susunan PHP/nginx server itu
sendiri.

## 1. Pasang Docker di server

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
```

Cek:
```bash
docker --version
docker compose version
```

Opsional, supaya tidak perlu `sudo` tiap perintah docker:
```bash
sudo usermod -aG docker $USER
```
(perlu logout/login ulang supaya berlaku)

## 2. DNS

Pastikan A record `dev-web.hasnurgroup.com` menunjuk ke `110.238.109.55`:
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

## 4. Siapkan `.env`

```bash
cp deploy/env.production.example backend/.env
```

Edit `backend/.env`, minimal:
```
APP_URL=https://dev-web.hasnurgroup.com
```

Jangan isi `DB_DATABASE` — `compose.yaml` sudah menetapkannya sendiri ke
`/app/data/database.sqlite` di dalam volume, terlepas dari isi `.env`.

Generate `APP_KEY` **tanpa perlu container menyala dulu** (container menolak
start kalau `APP_KEY` kosong, jadi kuncinya dibuat lebih dulu di luar):
```bash
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> backend/.env
```

Kalau backup terenkripsi mau dipakai (`deploy/backup.sh`), tambahkan juga:
```
BACKUP_ENCRYPTION_KEY=<isi kunci acak yang kamu simpan aman di tempat lain>
```

Isi juga akun Super Admin pertama — dibuat otomatis oleh `AdminUserSeeder`
saat basis data masih kosong, tidak perlu login SSH interaktif (lihat
langkah 8):
```
ADMIN_NAME="Nama Kamu"
ADMIN_EMAIL=admin@hasnurgroup.com
ADMIN_PASSWORD=<password_kuat_pilihan_kamu>
```
Password ini **tidak pernah** boleh ditulis di tempat lain selain `.env` di
server — bukan di kode, bukan di dokumen ini, bukan di chat mana pun. `.env`
sendiri sudah digitignore.

## 5. Build image

```bash
cd /var/www/html/web-jg
docker compose build
```

## 6. Nyalakan

```bash
docker compose up -d
```

Ini menyalakan dua container:
- `app` — nginx + php-fpm, melayani situs dan panel admin
- `penjadwal` — menjalankan `schedule:work` untuk backup harian dan
  pemangkasan data yang melewati masa simpan (pesan kontak, peristiwa
  analitik, riwayat versi artikel)

Karena ini nyala pertama, `entrypoint.sh` otomatis mengisi basis data (unit
bisnis, kantor, banner, artikel). Tunggu sampai selesai lalu cek log:
```bash
docker compose logs -f app
```
Berhenti menunggu (Ctrl+C) begitu muncul baris `NOTICE: ready to handle
connections` dan tidak ada galat di atasnya.

## 7. Verifikasi dari dalam server

Bawaan `compose.yaml`, container `app` terbuka di port `8080` host:
```bash
curl -I http://localhost:8080/
curl -I http://localhost:8080/admin
```
Kalau `beranda` menjawab 200 dan `/admin` menjawab 302 (redirect ke login),
aplikasinya sudah hidup.

## 8. Akun admin pertama

Kalau `ADMIN_EMAIL`/`ADMIN_PASSWORD` sudah diisi di `.env` sebelum nyala
pertama (langkah 4), akun Super Admin sudah otomatis terbuat oleh
`AdminUserSeeder` lewat proses seed di langkah 6 — tidak ada yang perlu
dijalankan lagi di sini.

Kalau server ini sudah pernah jalan lebih dulu (basis data sudah ada, jadi
seed otomatis tidak terulang), jalankan sekali manual:
```bash
docker compose exec app php artisan db:seed --class=AdminUserSeeder
```

Cara lama masih berfungsi sebagai alternatif kalau lebih suka mengisi
kredensial langsung lewat terminal interaktif, tanpa lewat `.env` sama
sekali:
```bash
docker compose exec app php artisan make:filament-user
docker compose exec app php artisan tinker --execute="App\Models\User::first()->update(['role' => 'super_admin'])"
```

## 9. Hubungkan domain + SSL

Container `app` cuma menjawab HTTP polos di port 8080 — belum tahu apa itu
`dev-web.hasnurgroup.com`, dan tidak mengurus sertifikat SSL sama sekali
(itu di luar tanggung jawab `docker/nginx.conf`, yang isinya cuma
`server_name _;`, lihat pembahasan sebelumnya). Cara paling umum: taruh nginx
**di host**, bukan di dalam container, sebagai reverse proxy yang menerima
HTTPS lalu meneruskan ke container.

Pasang nginx di host:
```bash
sudo apt install -y nginx
```

**Ubah dulu `compose.yaml`** supaya port container tidak langsung terbuka ke
internet — hanya bisa diakses dari host itu sendiri, lewat reverse proxy:
```yaml
    ports:
      - "127.0.0.1:${PORT_SITUS:-8080}:80"
```
Lalu:
```bash
docker compose up -d
```

Buat `/etc/nginx/sites-available/dev-web.hasnurgroup.com`:
```nginx
server {
    listen 80;
    server_name dev-web.hasnurgroup.com;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

`server_name` di sini yang menjawab pertanyaan sebelumnya: **wajib** diisi
nama domain, bukan IP, supaya (a) certbot tahu block mana yang harus
dipasangi SSL, dan (b) kalau nanti server ini melayani lebih dari satu
situs, nginx tahu request untuk domain ini harus diteruskan ke container
yang mana.

Aktifkan dan pasang SSL:
```bash
sudo ln -s /etc/nginx/sites-available/dev-web.hasnurgroup.com /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d dev-web.hasnurgroup.com
```

Setelah SSL aktif, tambahkan di `backend/.env`:
```
SESSION_SECURE_COOKIE=true
```
lalu `docker compose restart app` supaya nilainya kepakai (cache config
dibuat ulang otomatis tiap container start, lihat `entrypoint.sh`).

## 10. Verifikasi akhir

```bash
curl -I https://dev-web.hasnurgroup.com
```
Buka di browser: `https://dev-web.hasnurgroup.com` dan
`https://dev-web.hasnurgroup.com/admin`.

---

## Redeploy setelah ada perubahan kode

```bash
cd /var/www/html/web-jg
git pull
docker compose build
docker compose up -d
```

Migrasi baru otomatis jalan lagi lewat `entrypoint.sh` (idempotent — migrasi
yang sudah pernah jalan dilewati). Data di volume (`basis-data`, `unggahan`,
`varian`, `cadangan`) **tidak ikut hilang** saat build ulang; itulah gunanya
dipisah ke volume, bukan disalin ke dalam image.

## Yang harus dijaga tetap hidup — jangan pernah `docker compose down -v`

```bash
docker compose down       # aman: mematikan container, volume tetap ada
docker compose down -v    # BERBAHAYA: menghapus semua volume, termasuk basis-data dan unggahan admin
```

Volume `basis-data` menyimpan satu-satunya salinan basis data server ini.
Menghapusnya sama dengan kehilangan seluruh isi CMS yang pernah diisi lewat
panel admin sejak deploy pertama.

## Backup

Container `penjadwal` sudah menjalankan `deploy/backup.sh` lewat jadwal
internal aplikasi, menulis ke volume `cadangan`. Nama volume sebenarnya ikut
nama folder proyek di server — di `/var/www/html/web-jg` namanya jadi
`web-jg_cadangan`, bukan `shrimp_cadangan`. Cek nama pastinya:
```bash
docker volume ls | grep cadangan
```

Untuk menyalin hasilnya keluar dari Docker ke penyimpanan lain (disarankan,
volume Docker di satu mesin bukan backup off-site):
```bash
docker run --rm -v web-jg_cadangan:/cadangan -v $(pwd)/backup-keluar:/keluar \
  alpine cp -r /cadangan/. /keluar/
```

---

## Troubleshooting singkat

| Gejala | Kemungkinan penyebab |
|---|---|
| Container langsung mati, log bilang `APP_KEY belum disetel` | Lupa langkah 4 — `APP_KEY` harus ada di `backend/.env` sebelum `docker compose up` |
| `docker compose up` gagal, `port is already allocated` | Ada container lain (termasuk dari percobaan sebelumnya) masih memegang port 8080 — cek `docker ps -a`, matikan yang lama |
| Domain "can't be reached" tapi `curl localhost:8080` di server jalan | Reverse proxy (langkah 9) belum dipasang, atau firewall/security group Huawei Cloud belum membuka port 80/443 |
| Login admin gagal terus setelah pasang SSL | `SESSION_SECURE_COOKIE=true` di `.env` tapi belum `docker compose restart app`, cache config lama masih dipakai |
| Semua gambar 404 | Jarang terjadi di Docker (symlink dibuat otomatis tiap start) — kalau muncul, cek `docker compose logs app` untuk galat di `deploy/link-public-dirs.sh` |
| Basis data kosong lagi setelah deploy ulang | Kemungkinan besar volume ikut terhapus — cek riwayat perintah, jangan pernah `docker compose down -v` di server yang sudah berisi data asli |
