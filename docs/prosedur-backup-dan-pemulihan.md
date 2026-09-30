# Prosedur Backup dan Pemulihan

Menjawab CMS-11 pada TOR. Angka RPO dan RTO di bawah adalah **usulan**, bukan
kesepakatan: keduanya menentukan berapa banyak pekerjaan yang boleh hilang dan
berapa lama situs boleh padam, dan itu keputusan pemilik situs, bukan keputusan
teknis. Ubah angkanya setelah disepakati, lalu sesuaikan jadwalnya.

## Sasaran yang diusulkan

| | Usulan | Artinya |
|---|---|---|
| **RPO** (Recovery Point Objective) | 24 jam | Kehilangan paling banyak satu hari pekerjaan redaksi. |
| **RTO** (Recovery Time Objective) | 4 jam | Situs kembali tayang dalam empat jam sejak kerusakan disadari. |

RPO 24 jam mengikuti ritme kerja yang sebenarnya: artikel terbit beberapa kali
seminggu, bukan beberapa kali sejam. Menurunkannya ke satu jam berarti backup
per jam, dan yang dibeli dengan itu adalah 23 jam kerja yang dalam prakteknya
tidak ada isinya.

RTO 4 jam adalah perkiraan jujur, bukan target pemasaran. Memulihkan berkas
memakan menit; sisanya adalah waktu sampai ada orang yang menyadari, menemukan
backup terakhir yang sehat, dan memastikan yang dipulihkan memang benar.

## Apa yang dicadangkan

Hanya dua hal, dan keduanya dipilih karena tidak ada di tempat lain mana pun:

1. **Basis data** — seluruh isi situs: artikel, unit bisnis, kantor, banner,
   lowongan, pesan masuk, jejak audit, riwayat versi.
2. **Gambar unggahan** (`content-migration/images`) — berkas yang diunggah lewat
   panel admin.

Yang **tidak** ikut: kode, `vendor`, `node_modules`, dan varian WebP. Semuanya
bisa dibangun ulang dari repositori. Memasukkannya hanya membuat backup besar
dan lambat tanpa menambah satu pun hal yang benar-benar bisa hilang.

## Jadwal

`deploy/backup.sh` berjalan **setiap hari pukul 02.15 WIB**, dijadwalkan lewat
`backend/routes/console.php` dan dijalankan container `penjadwal`.

Hasilnya masuk volume Docker `cadangan`, terpisah dari volume `basis-data`.
Pemisahan ini bukan kerapian: backup yang berada di volume yang sama dengan
basis datanya akan ikut hilang tepat pada kejadian yang membuatnya dibutuhkan.

Retensi 30 hari (`BACKUP_RETENTION_DAYS`). Keluaran tiap jalannya ditulis ke
`backend/storage/logs/backup.log`.

> **Yang belum ada, dan harus diadakan:** salinan di luar mesin. Backup hari ini
> masih berada di host yang sama dengan situsnya. Ia melindungi dari basis data
> yang rusak, penghapusan tak sengaja, dan migrasi yang gagal — tetapi tidak
> dari host yang hilang. Salin volume `cadangan` ke penyimpanan lain secara
> berkala; sampai itu ada, RTO 4 jam hanya berlaku selama hostnya masih hidup.

## Memeriksa bahwa backup memang berisi

Backup yang gagal diam-diam sama dengan tidak punya backup. Lakukan ini sebulan
sekali — tidak cukup melihat berkasnya ada:

```sh
docker compose exec penjadwal sh -c '
  F=$(ls -t /cadangan/db-*.sqlite.gz | head -1)
  gunzip -c "$F" > /tmp/cek.sqlite
  echo "berkas: $F"
  sqlite3 /tmp/cek.sqlite "select count(*) from posts"
  sqlite3 /tmp/cek.sqlite "select count(*) from business_units"
  rm -f /tmp/cek.sqlite'
```

Jumlah artikel harus masuk akal. Nol atau angka yang jauh lebih kecil daripada
yang terlihat di panel berarti backup menunjuk berkas yang salah — kekeliruan
yang pernah terjadi di proyek ini dan tidak menimbulkan galat apa pun.

## Memulihkan

1. **Hentikan aplikasinya** supaya tidak ada yang menulis selagi berkas ditukar.
   ```sh
   docker compose stop app penjadwal
   ```

2. **Pilih backup**, lalu periksa isinya dengan perintah di atas sebelum dipakai.

3. **Kembalikan basis data.**
   ```sh
   docker compose run --rm --entrypoint sh penjadwal -c '
     gunzip -c /cadangan/db-YYYYMMDD-HHMMSS.sqlite.gz > /app/data/database.sqlite
     chown www-data:www-data /app/data/database.sqlite'
   ```

4. **Kembalikan gambar unggahan**, bila ikut hilang.
   ```sh
   docker compose run --rm --entrypoint sh penjadwal -c '
     tar -xzf /cadangan/unggahan-YYYYMMDD-HHMMSS.tar.gz -C /app'
   ```

5. **Nyalakan lagi.** Entrypoint menjalankan migrasi yang belum jalan, jadi
   backup dari versi kode lama tetap bisa dipakai pada kode baru.
   ```sh
   docker compose up -d
   ```

6. **Periksa hasilnya** lewat situs dan panel: jumlah artikel, gambar terbuka,
   dan login admin berfungsi.

## Di luar Docker

Skrip yang sama berjalan lewat cron:

```
15 2 * * * /path/ke/deploy/backup.sh >> /var/log/hasnur-backup.log 2>&1
```

Butuh `bash`, `sqlite3`, `tar`, dan `gzip`. Setel `BACKUP_DIR` ke folder di luar
document root.
