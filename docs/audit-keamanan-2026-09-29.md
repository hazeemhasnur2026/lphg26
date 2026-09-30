# Audit Keamanan untuk Pemakaian Enterprise

Per 29 September 2026. Laravel 13.33, Filament 4.13, PHP 8.4+, SQLite, Docker
(nginx + php-fpm dalam satu container).

Pemeriksaan dilakukan atas kode dan konfigurasi yang ada di repositori, bukan
atas server produksi — server produksinya belum ada.

---

## Ringkasan

| Tingkat | Jumlah | Butir |
|---|:-:|---|
| 🔴 Tinggi | 2 | Stored XSS (**sudah diperbaiki hari ini**), eksekusi PHP di folder unggahan |
| 🟠 Sedang | 5 | CSP belum ditegakkan, MFA, enkripsi backup, staging, pemindaian dependensi |
| 🟡 Rendah | 2 | Panel terbuka dari internet, jejak audit hanya di lapisan aplikasi |
| ✅ Baik | 17 | Lihat bagian terakhir |

> **Koreksi 29 September 2026, setelah pengujian dinamis.** Versi pertama
> laporan ini menyebut CSP dan HSTS tidak ada. Keduanya keliru: keduanya
> dipasang `app/Http/Middleware/SecurityHeaders.php`, dan saya hanya memeriksa
> `docker/nginx.conf` — padahal komentar di berkas itu sendiri menunjuk ke
> middleware tersebut. Temuan M-1 diturunkan menjadi "CSP belum ditegakkan",
> M-2 dicabut. Satu temuan baru ditambahkan (M-7) dan L-1 dinaikkan setelah
> terbukti dapat dieksploitasi, lalu ditutup.

Satu temuan tinggi sudah ditutup. Satu lagi perlu satu blok di `nginx.conf`.

---

## 🔴 Tinggi

### H-1 — Stored XSS di badan artikel — **SUDAH DIPERBAIKI**

**Keadaan sebelumnya.** `resources/views/posts/show.blade.php` mencetak badan
artikel dengan `{!! !!}` tanpa penyaringan apa pun. Diuji dengan menyimpan
`<script>window.__UJI=1</script>` dan `<img src=x onerror=…>`; keduanya lolos
utuh ke halaman yang dilayani.

**Siapa yang bisa memakainya.** Siapa pun yang boleh mengisi badan artikel,
termasuk Kontributor — peran paling rendah. Alur persetujuan tidak menolong:
peninjau membaca tulisan, bukan markup.

**Dampak.** Cookie sesi `httpOnly`, jadi sesi tidak bisa dicuri lewat
`document.cookie`. Tetapi skripnya berjalan di domain yang sama: ia dapat
membaca token CSRF dari DOM lalu bertindak atas nama siapa pun yang sedang
login, merusak tampilan, atau mengalihkan pengunjung ke halaman palsu.

**Perbaikan.** `App\Support\SafeHtml` menyaring dengan daftar putih memakai
`symfony/html-sanitizer` (sudah ada sebagai dependensi Laravel, tidak ada paket
baru). Dipanggil di `Post::getBodyHtmlAttribute()` sebelum apa pun yang lain.
Isi CSS pada atribut `style` ikut disaring ke 15 properti tipografi, karena
Symfony menyaring tag dan atribut tetapi tidak menyaring CSS — dan satu `div`
dengan `position:fixed` selebar layar cukup untuk memasang tampilan palsu tanpa
sebaris JavaScript.

Diuji terhadap 16 muatan serangan, nol lolos. 20 tes regresi ditambahkan.

### H-2 — Berkas `.php` di folder unggahan dieksekusi — **SUDAH DIPERBAIKI**

> **Terbukti, bukan dugaan.** Diuji 29 September 2026 di dalam container:
> sebuah berkas `.php` yang ditaruh di `content-migration/images/` (tujuan
> unggahan) dan di `assets/` dijawab **200 dengan isinya dijalankan**.
> Sesudah perbaikan, ketiga bentuk — `.php`, `.phtml` di kedua folder —
> dijawab **403**, dan 12 rute situs tetap 200.

**Keadaan.** `docker/nginx.conf:47` memakai pola lebar:

```nginx
location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

Aturan ini cocok dengan **berkas `.php` mana pun di bawah document root**.
Document root memuat tiga symlink, dan salah satunya adalah tujuan unggahan:

```
backend/public/content-images      → content-migration/images     ← tujuan unggahan
backend/public/content-images-opt  → content-migration/images-opt
backend/public/assets              → assets/
```

Permintaan ke `/content-images/pustaka/apa-pun.php` akan diteruskan ke php-fpm
dan dijalankan bila berkasnya ada.

**Yang menahan hari ini.** Seluruh komponen unggah membatasi tipe MIME, dan SVG
ditolak di semua tempat. Jadi belum ada jalan yang diketahui untuk menaruh
berkas `.php` di sana. Yang membuat ini tetap layak diperbaiki adalah
ketergantungannya: keamanan bergantung sepenuhnya pada validasi unggahan tidak
pernah longgar, di semua komponen, selamanya. Satu field unggah baru yang lupa
membatasi tipe berubah menjadi eksekusi kode jarak jauh.

**Perbaikan yang diterapkan.** Satu-satunya `.php` yang sah di document root
adalah `index.php`. Polanya dipersempit dan folder unggahan ditutup:

```nginx
location = /index.php {
    fastcgi_pass 127.0.0.1:9000;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    fastcgi_hide_header X-Powered-By;
}

location ~ ^/(content-images|content-images-opt|assets)/ {
    location ~ \.(php|phtml|phar)$ { deny all; }
}
```

---

### H-3 — Aset statis kehilangan header keamanan — **SUDAH DIPERBAIKI**

Ditemukan saat pengujian yang sama. `add_header` pada nginx **tidak diwariskan**
ke blok yang punya `add_header` sendiri: begitu blok cache aset menambahkan
`Cache-Control`, ketiga header di level server berhenti terkirim untuk setiap
gambar, CSS, dan JS.

Terukur sebelum perbaikan: halaman lewat PHP membawa `X-Content-Type-Options`,
aset statis **nol**. Yang paling merugikan hilangnya adalah `nosniff`, justru
pada berkas unggahan — tanpa itu peramban boleh menebak sendiri tipe berkas yang
diunggah orang, dan tebakan yang salah menjalankan isinya.

Diperbaiki dengan mengulang ketiga header di dalam blok aset. Terverifikasi:
`nosniff` kini terkirim pada `/assets/img/logo.png` dan `/build/assets/*.css`.

## 🟠 Sedang

### M-1 — Content-Security-Policy masih Report-Only

CSP **ada**, dan kebijakannya sudah rapat: `default-src 'self'`,
`frame-ancestors 'none'`, `object-src 'none'`, `base-uri 'self'`. Yang belum
adalah penegakannya — header yang dikirim `Content-Security-Policy-Report-Only`,
sehingga pelanggaran dilaporkan tetapi tidak dihentikan.

Keputusan itu sadar dan terdokumentasi di kelas middlewarenya: Alpine
mengevaluasi ekspresi lewat `new Function()`, panel admin memuat blok skrip
inline dari Livewire, dan beranda membawa 102 atribut style inline. Menegakkan
tanpa memeriksa lebih dulu berarti mengganti risiko XSS dengan risiko situs
yang tidak berfungsi.

Yang perlu dikerjakan: buka situs dan panel di peramban, kumpulkan laporan
pelanggaran, lalu naikkan ke penegakan — kalau perlu dengan nonce per
permintaan untuk skrip inline.

### M-3 — Tidak ada autentikasi dua faktor

Filament 4 membawa MFA bawaan; panel tidak mengaktifkannya. Untuk akun yang
dapat menerbitkan di situs korporat dan membaca data pribadi pengunjung, kata
sandi saja tipis.

Pembatas percobaan login ada dan berfungsi: lima percobaan
(`Filament\Auth\Pages\Login` memakai `WithRateLimiting`, `rateLimit(5)`).

### M-4 — Backup tidak dienkripsi

`deploy/backup.sh` menghasilkan `.sqlite.gz` dan `.tar.gz` polos. Isinya seluruh
basis data termasuk pesan dari formulir kontak — nama, surel, telepon, dan
alamat IP pengunjung. Siapa pun yang dapat membaca volume backup dapat membaca
semuanya.

### M-5 — Tidak ada environment staging

Hanya pengembangan dan produksi. TOR meminta pemisahan Development/Staging/
Production (TECH-13). Tanpa staging, perubahan diuji langsung di produksi atau
tidak diuji sama sekali.

### M-7 — Eskalasi hak lewat pengisian massal — **SUDAH DIPERBAIKI**

Terbukti dapat dieksploitasi saat pengujian dinamis: `$user->update(['role' =>
'super_admin'])` berhasil menaikkan peran seorang Kontributor. Tidak ada rute
hari ini yang meneruskan masukan mentah ke `update()`, jadi belum ada jalan
lewat HTTP — tetapi kolom yang menentukan hak akses terbuka bagi setiap baris
kode yang kelak ditulis.

Ditutup dengan penjaga di model, bukan dengan mengeluarkan kolomnya dari
`fillable`, supaya berlaku lewat jalur mana pun tanpa perlu diingat.

Versi pertama penjaga itu sendiri punya lubang, dan ditemukan lewat pengujian:
ia membaca peran pelaku dari objek di memori, sedangkan saat seseorang
menaikkan perannya sendiri objek itu **sudah** berisi peran baru yang ia
berikan kepada dirinya. Penjaga bertanya kepada penyerang yang sudah menyamar,
dan jawabannya selalu ya. Sekarang peran pelaku dibaca dari nilai tersimpan.

### M-6 — Dependensi tidak dipindai otomatis

Hari ini bersih — `composer audit` dan `npm audit` keduanya nol temuan. Tetapi
tidak ada yang memeriksanya besok: CI tidak menjalankan audit, dan tidak ada
`dependabot.yml`. Kerentanan baru pada paket yang sudah terpasang tidak akan
diketahui siapa pun.

---

## 🟡 Rendah

### L-2 — Panel admin terbuka dari internet

`/admin` dapat dicapai siapa pun. Untuk situs korporat, membatasinya ke rentang
IP kantor atau menempatkannya di belakang VPN akan menghapus seluruh kelas
serangan penebakan kata sandi.

### L-3 — Jejak audit hanya kebal di lapisan aplikasi

`AuditLogResource` menolak sunting dan hapus, dan tidak ada rute lain yang
menyentuhnya. Tetapi tidak ada yang menahan di lapisan basis data. Untuk jejak
audit yang dipakai sebagai bukti kepatuhan, pertimbangkan akun basis data
terpisah tanpa hak `UPDATE`/`DELETE` pada tabel itu, atau pengiriman salinan ke
penyimpanan hanya-tambah di luar aplikasi.

---

## ✅ Yang sudah baik

| | Bukti |
|---|---|
| Dependensi bersih | `composer audit` dan `npm audit`: nol kerentanan |
| CSRF aktif | `PreventRequestForgery` pada seluruh middleware panel |
| Cookie sesi | `httpOnly` menyala, terenkripsi, `SameSite=lax`, `secure` di produksi |
| Deploy menolak setelan berbahaya | `after-deploy.sh` berhenti bila `APP_DEBUG≠false`, `APP_ENV≠production`, atau `SESSION_SECURE_COOKIE≠true` |
| Pembatas percobaan login | Lima percobaan, bawaan Filament |
| Formulir kontak dibatasi | `throttle:5,10` |
| SVG ditolak | Tidak diterima di satu pun komponen unggah — vektor XSS lewat gambar tertutup |
| Tidak ada rahasia ter-commit | `git ls-files` bersih dari `.env`, `.pem`, `.key` |
| SQL mentah tanpa masukan pengguna | Seluruh `orderByRaw` berisi string tetap |
| Wildcard pencarian dilepas | `%` dan `_` di-escape sebelum masuk `LIKE` |
| Data pribadi punya masa simpan | Pesan kontak dipangkas otomatis setelah 24 bulan |
| Hak akses disaring di query | Kontributor tidak bisa membuka naskah orang lain walau URL ditebak |
| Berkas tersembunyi tidak dilayani | `location ~ /\.` menolak, termasuk `.env` bila tersalin ke public/ |
| CSP terpasang | `default-src 'self'`, `frame-ancestors 'none'`, `object-src 'none'`, `base-uri 'self'` — lihat M-1 soal penegakan |
| HSTS terpasang | Dikirim hanya saat permintaan sudah lewat HTTPS, sehingga pengembangan lokal tidak ikut terkunci |
| Permissions-Policy | Kamera, mikrofon, lokasi, dan FLoC dimatikan |
| Diuji dinamis | 12 berkas sensitif, 9 muatan injeksi, 4 percobaan path traversal, throttle, CSRF, brute force login — lihat bagian pengujian |

---

## Hasil pengujian dinamis

Dijalankan terhadap aplikasi yang berjalan di `127.0.0.1:8123`, 29 September 2026.

| Pengujian | Muatan | Hasil |
|---|:-:|---|
| Berkas sensitif terekspos | 15 | Semua 404/403 kecuali `/.htaccess` (200) — hanya pada server pengembangan; nginx menolak berkas titik |
| Injeksi SQL pada pencarian | 6 | Nol hasil, nol kebocoran galat. `%` dan `_` terbukti di-escape |
| XSS pada pencarian | 1 | Tidak tereksekusi |
| Path traversal | 4 | Semua 404, termasuk bentuk terenkode `%2e%2e` dan `%2f` |
| Metode HTTP | 5 | TRACE 405, TRACK 501, DELETE/PUT 405 |
| Throttle formulir kontak | 7 kiriman | Lima diterima, keenam 429 — persis batas yang disetel |
| CSRF | 1 | POST tanpa token ditolak 419 |
| Brute force login panel | 7 percobaan | Dibatasi pada percobaan keenam |
| IDOR antar kontributor | — | Naskah kontributor lain tidak terlihat |
| Eskalasi hak | — | **Tembus** sebelum diperbaiki; lihat M-7 |
| Kebocoran draf | — | Draf tidak masuk scope publik |
| Retensi data pribadi | — | Pesan lewat masa simpan benar-benar terhapus, yang baru bertahan |

### Pengujian di dalam container nginx

Dijalankan setelah container dinyalakan, pada hari yang sama.

| Pengujian | Sebelum | Sesudah |
|---|---|---|
| `.php` di `/content-images/` | **200, dieksekusi** | 403 |
| `.php` di `/assets/` | **200, dieksekusi** | 403 |
| `.phtml` di `/content-images/` | belum diuji | 403 |
| `nosniff` pada aset statis | **hilang** | terkirim |
| `X-Powered-By` | tersembunyi | tersembunyi |
| `.env`, `.htaccess`, `.git/HEAD` | 403 | 403 |
| 12 rute situs | 200 | 200 |
| Livewire dan aset build | 200 | 200 |

Berkas uji hanya mencetak satu kata dan dihapus segera setelah diuji.

## Alat yang disarankan

Yang sudah dipakai di sini: `composer audit`, `npm audit`, `curl`, dan probe
tertulis sendiri. Yang layak ditambahkan, berurutan menurut rasio manfaat
terhadap usaha:

| Alat | Untuk | Catatan |
|---|---|---|
| **`composer audit` + `npm audit` di CI** | Kerentanan dependensi | Lima baris di `ci.yml`. Tanpa ini, tidak ada yang memeriksa besok |
| **Dependabot** | Pembaruan otomatis | `.github/dependabot.yml`, satu berkas |
| **Larastan** (`larastan/larastan`) | Analisis statis khusus Laravel | Menangkap kelas kesalahan yang tidak muncul di tes |
| **`enlightn/enlightn`** | Pemindai keamanan khusus Laravel | Memeriksa 60+ hal: konfigurasi, header, kebocoran, kinerja |
| **OWASP ZAP** (baseline scan) | Pemindaian dinamis | Bisa dijalankan di CI terhadap lingkungan staging |
| **`nikto`** | Salah konfigurasi server web | Berguna justru untuk menguji nginx, tempat temuan H-2 berada |
| **Trivy** | Kerentanan pada image Docker | Memindai lapisan sistem, bukan hanya dependensi aplikasi |

Yang **tidak** saya sarankan untuk situs ini: sqlmap dan pemindai serangan
otomatis lain terhadap produksi. Seluruh kueri memakai Eloquent dengan
parameter terikat, hasil probe manual bersih, dan pemindai semacam itu
menghasilkan lalu lintas yang sulit dibedakan dari serangan sungguhan di log —
termasuk oleh tim yang memantau.

## Urutan yang disarankan

1. **H-2** — persempit `location ~ \.php$` dan tutup folder unggahan. Satu blok
   nginx, dan menghapus satu-satunya jalan menuju eksekusi kode.
2. **M-6** — jalankan `composer audit` dan `npm audit` di CI. Lima baris, dan
   sejak itu setiap kerentanan baru muncul sendiri.
3. **M-4** — enkripsi backup. Data pribadi pengunjung ada di dalamnya.
4. **M-3** — nyalakan MFA untuk peran Admin ke atas.
5. **M-1** — CSP dengan nonce. Paling banyak pekerjaannya, dan paling baik
   dikerjakan setelah empat di atas selesai.
