# Prosedur Penanganan Kebocoran Data Pribadi

**Berlaku untuk:** situs hasnurgroup.com dan panel administrasinya
**Dasar:** UU No. 27 Tahun 2022 Pasal 46, yang mewajibkan pemberitahuan kepada subjek
data dan lembaga berwenang paling lambat **3x24 jam** sejak kebocoran diketahui
**Status:** draf. Bagian bertanda `[perlu diisi]` menunggu penunjukan dari manajemen.

---

## Yang dihitung sebagai kebocoran

Kebocoran adalah setiap kejadian yang membuat data pribadi terbaca, berubah, hilang,
atau tersebar tanpa hak. Pada situs ini, data pribadi yang ada hanya di tiga tempat:

| Tempat | Isi |
|---|---|
| Tabel `contact_messages` | nama, surel, nomor telepon, isi pesan, alamat IP pengirim |
| Tabel `users` | nama, surel, dan kata sandi pengelola dalam bentuk teracak |
| Tabel `sessions` | alamat IP dan user agent pengelola yang sedang masuk |

Contoh kejadian yang termasuk: basis data tersalin keluar, akun administrator dipakai
orang yang tidak berhak, backup jatuh ke tangan pihak lain, pesan masuk terkirim ke
alamat yang salah, atau berkas basis data dapat diunduh dari internet.

Contoh yang **bukan** kebocoran: situs tidak bisa diakses, galat aplikasi tanpa data
yang terlihat, atau percobaan masuk yang gagal.

---

## Tahapan

```
Terdeteksi  ->  Tahan  ->  Nilai  ->  Laporkan  ->  Pulihkan  ->  Catat
   jam 0       < 2 jam    < 12 jam    < 3x24 jam    setelahnya   setelahnya
```

### 1. Terdeteksi

Siapa pun yang melihat tandanya segera menghubungi `[perlu diisi: nama dan nomor
penanggung jawab]`. Jangan menunggu kepastian. Laporan yang ternyata salah jauh lebih
murah daripada tiga hari yang terbuang.

Catat jam saat kejadian diketahui. Jam inilah yang menjadi titik awal tenggat 3x24 jam,
bukan jam saat kebocorannya terjadi.

### 2. Tahan, dalam 2 jam pertama

Tujuannya menghentikan kebocoran, bukan mencari penyebab.

- Cabut sesi yang sedang berjalan: `php artisan session:flush` atau kosongkan tabel
  `sessions`.
- Ganti kata sandi seluruh akun panel administrasi.
- Putar `APP_KEY` bila ada dugaan berkas `.env` ikut terbaca. Perlu diingat: memutar
  `APP_KEY` membuat seluruh sesi dan cookie lama tidak terbaca lagi, dan itu memang
  yang diinginkan.
- Cabut kunci SSH deploy di GitHub bila jalur masuknya lewat pipeline.
- **Jangan menghapus log.** Log adalah bukti, dan menghapusnya menyulitkan penilaian
  sekaligus melemahkan posisi perusahaan.

### 3. Nilai, dalam 12 jam

Jawab lima pertanyaan ini secara tertulis:

1. Data apa saja yang terpapar, dan milik berapa orang?
2. Sejak kapan sampai kapan?
3. Siapa yang memperolehnya, bila diketahui?
4. Apa akibat yang mungkin dialami orang yang datanya terpapar?
5. Apakah kebocorannya sudah benar-benar berhenti?

Sumber bukti: log aplikasi di `backend/storage/logs`, log akses web server, riwayat
login panel, dan riwayat `git log` bila menyangkut perubahan kode.

### 4. Laporkan, sebelum 3x24 jam

Dua pemberitahuan terpisah, keduanya tertulis.

**Kepada subjek data yang terpapar**, memuat: data apa yang terpapar, kapan
terjadinya, apa akibat yang mungkin timbul, apa yang sudah perusahaan lakukan, dan apa
yang sebaiknya mereka lakukan. Alamat surel pengirim pesan tersimpan di
`contact_messages`, jadi penerima pemberitahuan dapat ditelusuri dari sana.

**Kepada lembaga berwenang**, sesuai ketentuan pelaksana UU PDP yang berlaku saat
kejadian. `[perlu diisi: kanal dan format pelaporan yang berlaku, dikonfirmasi ke
penasihat hukum]`

Bila dalam 3x24 jam penilaian belum selesai, tetap kirim pemberitahuan awal dengan
keterangan yang sudah ada, lalu susulkan yang lengkap. Terlambat karena menunggu
kepastian tetap terlambat.

### 5. Pulihkan

- Tutup celah yang dipakai.
- Pulihkan data dari backup bila ada yang hilang. Backup harian ada di server, dibuat
  `deploy/backup.sh`, dan cara memulihkannya ada di bagian bawah.
- Jalankan ulang `php artisan test` dan kedua sapuan QA sebelum situs dinyatakan pulih.

### 6. Catat

Satu dokumen per kejadian, disimpan sekurang-kurangnya lima tahun:

```
Nomor kejadian      :
Diketahui pada      : tanggal dan jam
Dilaporkan oleh     :
Data yang terpapar  :
Jumlah orang        :
Penyebab            :
Tindakan penahanan  : jam berapa, oleh siapa
Pemberitahuan       : kepada siapa, kapan, lewat apa
Perbaikan permanen  :
Ditutup pada        :
```

Dokumen inilah yang diminta bila lembaga berwenang menanyakan akuntabilitas menurut
Pasal 47.

---

## Peran

| Peran | Siapa | Tugas |
|---|---|---|
| Penanggung jawab pelindungan data | `[perlu diisi]` | memutuskan, menandatangani pemberitahuan |
| Penanggung jawab teknis | `[perlu diisi]` | menahan, menilai, memulihkan |
| Penghubung legal | `[perlu diisi]` | bentuk dan kanal pelaporan |
| Penghubung komunikasi | `[perlu diisi]` | pertanyaan dari luar, bila ada |

Pada tim sekecil ini satu orang boleh memegang lebih dari satu peran, asalkan namanya
tertulis. Peran tanpa nama sama dengan tidak ada peran saat kejadian sungguhan.

---

## Memulihkan dari backup

```sh
# SQLite
gunzip -c /path/backup/db-YYYYMMDD-HHMMSS.sqlite.gz > /tmp/pulih.sqlite
sqlite3 /tmp/pulih.sqlite "select count(*) from contact_messages;"   # periksa dulu
cp /tmp/pulih.sqlite /path/backend/database/database.sqlite

# Gambar unggahan
tar -xzf /path/backup/unggahan-YYYYMMDD-HHMMSS.tar.gz -C /path/repo
```

Pemulihan diuji terakhir pada 24 September 2026 dengan hasil terbaca: 1 pesan,
36 artikel, 1 pengguna, dan 138 berkas unggahan. **Uji ulang setiap kuartal.** Backup
yang tidak pernah diuji bukan backup, hanya berkas yang diharapkan berguna.

---

## Pencegahan yang sudah berjalan

| Kontrol | Keadaan |
|---|---|
| Pemisahan peran | Editor tidak dapat membuka pesan masuk, dibatasi di menu sekaligus URL |
| Header keamanan | nosniff, DENY pembingkaian, Referrer-Policy, Permissions-Policy |
| Cookie | Wajib HTTPS di produksi, dan deploy menolak berjalan bila setelannya salah |
| Unggahan | Dibatasi jpeg, png, webp, maksimal 4 MB |
| Pembatasan laju | 5 kiriman formulir per 10 menit, dan pembatasan percobaan login |
| Masa simpan | Pesan dipangkas otomatis setelah masa simpan yang disetel |
| Backup | Harian, disimpan 30 hari |

| Yang belum ada | Akibatnya |
|---|---|
| MFA untuk akun panel | Kata sandi yang bocor cukup untuk masuk |
| Pemantauan dan peringatan | Kebocoran hanya ketahuan bila ada yang kebetulan melihat |
| Pencatatan akses ke pesan masuk | Tidak terlacak siapa membaca pesan siapa |
