# Panduan Panel Admin

Untuk Corporate Communication. Menjawab CMS-12 pada TOR, bagian panduan
tertulisnya; dua sesi pelatihan tetap perlu dijadwalkan terpisah.

Panel ada di `/admin`. Masuk dengan surel dan kata sandi yang dibuatkan Super
Admin.

---

## 1. Siapa boleh apa

Empat peran. Yang membedakan bukan seberapa penting orangnya, melainkan
seberapa jauh sebuah naskah boleh didorong sendirian.

| Peran | Menulis | Mengajukan | Menyetujui & menerbitkan | Pesan masuk | Kelola akun |
|---|:-:|:-:|:-:|:-:|:-:|
| **Kontributor** | naskah sendiri | ✓ | — | — | — |
| **Peninjau** | semua | ✓ | ✓ | — | — |
| **Admin Korporat** | semua | ✓ | ✓ | ✓ | — |
| **Super Admin** | semua | ✓ | ✓ | ✓ | ✓ |

Dua hal yang sering mengejutkan di awal:

- **Kontributor hanya melihat naskahnya sendiri.** Bukan disembunyikan dari
  daftar saja — naskah orang lain memang tidak bisa dibuka, termasuk lewat URL.
- **Kontributor tidak bisa menyunting naskahnya sendiri setelah diajukan.**
  Naskah yang sedang dibaca peninjau tidak boleh berubah di bawah tangannya.
  Minta peninjau mengembalikannya ke draf bila masih perlu diperbaiki.

---

## 2. Alur sebuah artikel

```
Draf ──ajukan──> Menunggu Tinjauan ──setujui──> Disetujui ──terbitkan──> Terbit
                         │                          │                      │
                         └──── kembalikan ──────────┴──────────────────────┘
                                   (ke Draf, wajib pakai alasan)                   Terbit ──arsipkan──> Diarsipkan
```

**Alurnya tidak bisa dilompati.** Bahkan Super Admin tidak bisa memindahkan draf
langsung ke Terbit; urutannya melekat pada status, bukan pada hak. Untuk
menerbitkan cepat: ajukan, setujui, terbitkan — tiga klik berturut-turut.

**Diarsipkan bukan dihapus.** Artikel ditarik dari situs, isinya tetap utuh, dan
bisa diterbitkan lagi kapan saja.

### Membuat artikel

1. Buka menu kategorinya di sidebar (**Publikasi → Berita**, dan seterusnya).
   Kategori langsung terisi benar bila masuk lewat menu ini.
2. **Buat**, lalu isi:
   - **Judul** — slug terisi otomatis, dan **hanya saat membuat baru**.
   - **Kategori** — menentukan halaman daftar dan bentuk URL.
   - **Tipe konten** — bentuk kontennya (Berita, Laporan, Galeri, …). Berbeda
     dari kategori: satu daftar boleh memuat beberapa tipe. Tipe tertentu
     memunculkan kolom tambahan, misalnya PDF untuk Laporan.
   - **Ringkasan** — tampil di kartu daftar.
   - **Isi artikel**.
3. **Simpan.** Artikel lahir sebagai **Draf**.
4. **Ajukan untuk ditinjau** dari menu titik tiga di daftar.

> **Jangan ubah slug artikel yang sudah terbit.** URL lamanya akan menjawab 404
> dan tautan yang sudah terindeks Google mati. Kalau memang harus, minta agar
> pengalihan 301 dipasang.

### Menjadwalkan

- **Tanggal terbit** di masa depan → artikel baru muncul setelah waktu itu lewat.
  Statusnya sudah Terbit, tapi belum tampil. Daftar menandainya *terjadwal*.
- **Berhenti tampil** → artikel hilang dari situs sendiri pada waktu itu.
  Kosongkan bila tidak punya masa berlaku.
- Semua waktu **WIB**.
- Menerbitkan artikel yang sudah dijadwalkan **tidak** menimpa jadwalnya.

### Pemberitahuan

Lonceng di kanan atas panel memberi tahu saat giliran Anda tiba:

| Peristiwa | Yang diberi tahu |
|---|---|
| Naskah diajukan | Semua Peninjau, Admin Korporat, dan Super Admin |
| Naskah dikembalikan ke draf | Penulisnya, **berikut alasannya** |
| Naskah disetujui | Penulisnya |
| Naskah terbit | Penulisnya |
| Naskah diarsipkan | Tidak ada — itu keputusan redaksi atas konten lama, bukan giliran orang lain |

Anda tidak diberi tahu tentang perbuatan Anda sendiri. Loncengnya diperiksa
ulang tiap satu menit, jadi pemberitahuan baru muncul tanpa perlu memuat ulang
halaman.

---

## 3. Bagian SEO

Semuanya boleh dikosongkan; masing-masing punya cadangan.

| Kolom | Bila kosong |
|---|---|
| Judul SEO | memakai judul artikel |
| Meta description | memakai ringkasan |
| Gambar berbagi (OG) | memakai gambar sampul |
| URL kanonik | memakai URL artikel sendiri |

**Meta description sebaiknya ditulis sendiri, tidak dibiarkan mengambil
ringkasan.** Keduanya untuk pembaca berbeda: ringkasan untuk orang yang sudah
ada di halaman daftar, meta description untuk orang yang belum memutuskan
mengklik di hasil pencarian. Sekitar 150 karakter.

**Jangan indeks halaman ini** mengeluarkan artikel dari sitemap sekaligus
meminta mesin pencari melewatinya.

---

## 4. Pustaka Media

**Pustaka → Pustaka Media.** Tempat semua berkas: gambar, video, dan PDF.

Unggah sekali, pakai berkali-kali. Foto untuk galeri **diambil dari pustaka**,
bukan diunggah per artikel — mengunggah ulang melahirkan salinan ketiga dari
berkas yang sama, masing-masing dengan alt text yang harus ditulis ulang.

Isi saat mengunggah:

- **Alt text** — apa yang terlihat di gambar, untuk pembaca layar dan saat
  gambar gagal dimuat. Kosongkan untuk PDF dan video.
- **Folder** — pengelompokan bebas, misalnya `csr-2026`.
- **Tag** — untuk dicari nanti.

Penyaring **Belum ada alt text** menunjukkan gambar yang masih perlu
dilengkapi. Angkanya juga muncul di dasbor.

Gambar lama dari situs sebelumnya bisa didaftarkan sekaligus oleh tim teknis
dengan `php artisan media:import`. Setelah itu berkasnya muncul di pustaka
dengan alt text kosong, siap dilengkapi redaksi.

> Setelah mengunggah gambar baru, jalankan `tools/optimize_images.py --tree content`
> agar versi WebP-nya ikut dibuat. Tanpa itu gambarnya tetap tampil, hanya lebih
> berat.

---

## 5. Riwayat versi

Setiap penyimpanan yang **benar-benar mengubah isi** membuat satu versi. Membuka
lalu menyimpan tanpa mengubah apa pun tidak menambah versi.

Buka artikel → tab **Riwayat Versi** → **Kembalikan**.

Yang disimpan **50 versi terakhir per artikel**; selebihnya dipangkas otomatis
tiap pekan. Batasnya per jumlah, bukan per umur — artikel yang disunting sepuluh
kali sebulan akan kehilangan seluruh riwayatnya sekaligus kalau batasnya umur,
sementara yang disunting setahun sekali menyimpan satu versi selamanya.

Yang dikembalikan hanya **tulisannya**: judul, isi, ringkasan, gambar, kolom SEO.
Status, tanggal terbit, dan catatan tinjauan tidak ikut — mengembalikan tulisan
bukan alasan untuk membatalkan persetujuan orang lain.

Pengembalian menyimpan dulu isi yang sedang berlaku sebagai versi baru, jadi
pengembalian yang keliru bisa dibatalkan dengan cara yang sama.

---

## 6. Jejak Audit

**Pengaturan → Jejak Audit.** Mencatat pembuatan, penyuntingan, perpindahan
status, pengembalian versi, dan penghapusan: siapa, apa, kapan, dari IP mana.

**Tidak bisa disunting dan tidak bisa dihapus oleh siapa pun**, termasuk Super
Admin. Jejak audit yang bisa dirapikan tidak menjawab apa pun saat dibutuhkan.

Nama pelaku ikut disalin saat peristiwa terjadi, jadi jejaknya tetap terbaca
setelah akunnya dihapus.

---

## 7. Dasbor

Menjawab "apa yang perlu saya kerjakan hari ini", bukan "berapa banyak yang kita
punya":

- **Menunggu tinjauan** — antrean naskah. Angka yang sama muncul di sidebar.
- **Draf** — belum diajukan.
- **Terjadwal** — akan terbit sendiri.
- **Tanpa tanggal** — artikel hasil migrasi yang tanggalnya belum dipastikan.
  Artikelnya **tetap tampil** di situs; ini pekerjaan yang tertunda, bukan
  kerusakan.
- **Gambar tanpa alt text** — muncul hanya bila ada.
- **Pesan belum ditangani** — hanya untuk Admin Korporat ke atas.

---

## 8. Analitik

**Pengaturan → Analitik.** Memperlihatkan apa yang benar-benar diklik pengunjung
selama 30 hari terakhir: tombol ajakan, berkas yang diunduh, kata kunci yang
diketik di halaman pencarian, dan artikel yang dibaca sampai habis.

Tiga bagian: kartu angka di atas, grafik harian di tengah (rentangnya bisa
diganti 7 / 30 / 90 hari), dan lima tabel peringkat di bawah.

**Dua angka pada tiap tabel peringkat, dan keduanya berbeda arti.**
*Peristiwa* adalah jumlah klik. *Pengunjung* adalah berapa orang berbeda yang
melakukannya. "Diklik sepuluh kali" oleh satu orang bukan kabar yang sama dengan
"diklik sepuluh orang", dan hanya yang kedua berarti sesuatu tentang
ketertarikan.

**Kata kunci yang paling sering dicari adalah bagian yang paling layak dibaca
rutin.** Kata yang sering diketik orang biasanya berarti halamannya sulit
ditemukan dari menu — itu daftar pekerjaan, bukan sekadar angka.

**Yang tidak bisa dijawab halaman ini:** dari mana pengunjung datang, kata kunci
apa yang membawanya dari Google, pengunjung baru atau yang kembali, dan
perbandingan dengan situs lain. Itu pekerjaan platform analitik seperti Google
Analytics, yang menunggu keputusan platform mana yang dipakai (CMS-10 pada TOR).
Angka di halaman ini dihitung sendiri oleh situs.

**Kunjungan tim sendiri ikut terhitung.** Tidak ada penyaring untuk itu, jadi
hari kerja yang ramai penyuntingan cenderung terlihat sedikit lebih ramai
daripada perhatian pengunjung yang sebenarnya.

**Bila muncul kotak "Papan ini memuat data contoh"**, angka di bawahnya bercampur
dengan data buatan untuk keperluan peninjauan dan belum boleh dipakai mengambil
keputusan. Minta IT menjalankan `php artisan analitik:contoh --hapus`.

Yang boleh membukanya: Peninjau ke atas. Kontributor tidak, karena halaman ini
memuat kata yang diketik pengunjung.

---

## 9. Pertanyaan yang sering muncul

**Angka di Analitik tidak bertambah padahal saya sudah mengklik.**
Muat ulang halaman publiknya lebih dulu, lalu klik lagi. Tab yang dibiarkan
terbuka berjam-jam memakai token keamanan yang sudah kedaluwarsa, dan peristiwa
dari tab itu ditolak server. Bila masih tidak bertambah, buka Console peramban
(F12) di halaman publiknya: pesan yang diawali `[analitik]` menyebutkan alasan
penolakannya.

**Artikel saya tidak muncul di situs.**
Periksa berurutan: statusnya Terbit? Tanggal terbitnya sudah lewat? "Berhenti
tampil" belum terlampaui?

**Saya tidak bisa menyunting artikel saya sendiri.**
Kemungkinan sudah diajukan. Minta peninjau mengembalikannya ke draf.

**Saya salah menghapus artikel.**
Penghapusan permanen dan membawa seluruh riwayat versinya. Yang tersisa hanya
catatan di Jejak Audit. Untuk menarik artikel dari situs, gunakan **Arsipkan**,
bukan Hapus.

**Mengubah slug artikel lama, boleh?**
Sebaiknya jangan. Lihat peringatan di bagian 2.
