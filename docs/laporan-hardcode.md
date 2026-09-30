# Laporan Nilai Hardcode

Per 1 Oktober 2026. Hasil pemeriksaan sistematis (`grep` menyeluruh, bukan
sampling) atas `backend/app`, `backend/resources/views`, dan `backend/config`.
Tidak termasuk `vendor/` dan berkas bawaan Laravel (`welcome.blade.php`).

Urutan berdasarkan risiko, bukan urutan ditemukan.

---

## 🔴 Tinggi — butuh keputusan manusia, bukan sekadar refactor

### H-1. Kebijakan Privasi publik punya 4 placeholder `[perlu keputusan]`

**Berkas:** `resources/views/pages/legal/kebijakan-privasi.blade.php`

Halaman ini **sudah tayang di situs publik** (`/page/legal/kebijakan-privasi`)
dengan empat bagian belum terisi:

| Baris | Yang belum diputuskan |
|---|---|
| 24 | Kotak peringatan sendiri: "Dokumen ini belum final" |
| 34 | Nama dan kontak penanggung jawab pelindungan data |
| 56 | Dasar pemrosesan menurut Pasal 20 UU No. 27/2022 (PDP) |
| 103 | Masa simpan pesan formulir & jadwal penghapusan otomatis |

**Ini bukan bug yang lolos tanpa disadari** — ada komentar Blade eksplisit di
baris 18-20: *"Kotak ini harus hilang sebelum situs diakses publik."* Artinya
sudah diketahui, tapi tetap tayang.

**Ironi teknisnya:** baris 103 meminta "masa simpan pesan formulir" dicantumkan
manual, padahal nilainya **sudah ada** dan bisa diambil hidup dari
`config('site.retensi_pesan_bulan')` — kolom yang sama yang benar-benar dipakai
`ContactMessage::scopeExpired()` untuk memangkas data. Menulisnya ulang sebagai
teks statis berarti kedua nilai itu bisa berselisih di kemudian hari tanpa ada
yang sadar.

**Yang harus dikerjakan:**
- Manajemen + penasihat hukum mengisi 3 baris substantif (34, 56, 103)
- Baris 103 sebaiknya diganti `{{ config('site.retensi_pesan_bulan') }} bulan`
  alih-alih angka tertulis, supaya kedua tempat tidak pernah berselisih
- Kotak peringatan (24) dihapus setelah ketiganya final

---

### H-2. Nilai Inti perusahaan (7 warna + teks) hanya ada di kode, tidak di CMS

**Berkas:** `resources/views/home.blade.php:111-117`

```php
['name' => 'Pantang Menyerah', 'color' => '#DA272C', 'description' => '...'],
['name' => 'Keadilan',         'color' => '#83C034', 'description' => '...'],
// ...5 lagi
```

Tujuh nilai inti Hasnur Group — nama, warna badge, dan deskripsi panjang —
ditulis sebagai array PHP literal di dalam view beranda. **Tidak ada satu pun
jalan di CMS untuk mengubahnya.** Mengganti urutan, memperbaiki salah ketik
pada deskripsi, atau mengubah warna satu nilai berarti mengedit kode dan
men-deploy ulang.

Ini beda sifatnya dari H-1: bukan konten yang belum diputuskan, tapi konten
yang *sudah* final namun ditanam permanen di tempat yang salah untuk sesuatu
yang berlabel CMS.

**Yang harus dikerjakan:** kalau nilai inti dianggap konten yang bisa berubah
(rebranding, penyesuaian bahasa), pindahkan ke tabel sendiri (`core_values`)
dan resource Filament — pola yang sama seperti `BusinessUnit`. Kalau dianggap
identitas tetap perusahaan yang memang tidak boleh diubah editor, ini bukan
hardcode yang perlu dibetulkan — cukup didokumentasikan sebagai keputusan
sadar.

---

## 🟠 Sedang — bukan salah, tapi rapuh terhadap perubahan yang wajar

> **Status 1 Oktober 2026: M-1 sampai M-4 sudah diperbaiki dan diverifikasi**
> lewat 8 tes baru (`BusinessUnitNavigationTest`, `CentralizedConfigTest`).
> 216 tes penuh, semuanya hijau. Detail perbaikan di tiap butir di bawah.

### M-1. Menu navigasi 7 unit bisnis ditulis manual, dobel (desktop + mobile) — ✅ DIPERBAIKI

**Berkas:** `resources/views/partials/header.blade.php:35-41` dan `152-158`

```blade
<a href="/page/our-business/logistic">{{ __('nav.business.logistic') }}</a>
<a href="/page/our-business/agribusiness-forestry">{{ __('nav.business.agribusiness') }}</a>
<!-- ...5 baris lagi, lalu diulang sama persis untuk menu mobile -->
```

Tabel `business_units` sudah ada dan sudah dipakai kartu SBU di beranda
(`BusinessUnit::ordered()->get()`), tapi menu navigasi header **tidak
membacanya** — 7 tautan ditulis tangan, lalu diulang identik untuk versi
mobile. Hari ini jumlahnya kebetulan cocok (7 unit = 14 baris menu). Begitu
seseorang menambah atau menghapus unit bisnis lewat panel, menu navigasi tidak
ikut berubah — akan menunjuk unit yang sudah dihapus, atau melewatkan unit
baru.

**Yang dikerjakan:** `View::composer('partials.header', ...)` membagikan
`BusinessUnit::ordered()->get()`, kedua blok (desktop + mobile) diganti
`@foreach`. Satu kerumitan yang baru terlihat saat mengerjakannya:
`BusinessUnit->name` bukan kolom translatable, sedangkan menu ini muncul di
tiga bahasa. Label karena itu tetap dari `lang/*.json`, dipetakan lewat
`config('site.business_unit_nav_keys')` — slug tidak selalu sama bentuk
dengan kunci terjemahannya (`agribusiness-forestry` → kunci `agribusiness`).
Unit yang lupa didaftarkan di pemetaan jatuh ke `$unit->name` apa adanya,
bukan pecah atau salah menunjuk kunci unit lain.

---

### M-2. Tautan sosial media ditulis di 3 tempat terpisah — ✅ DIPERBAIKI

**Berkas:**
- `resources/views/partials/footer.blade.php` (ikon di footer)
- `app/Support/StructuredData.php` (skema `sameAs` untuk SEO)
- `app/Console/Commands/SeedAnalyticsSample.php` (data contoh)

Ketiga tempat menulis ulang URL LinkedIn, Instagram, dan YouTube yang sama.
Satu perbedaan kecil sudah kelihatan: `SeedAnalyticsSample.php` menulis
`linkedin.com/company/hasnur-group` (dengan tanda hubung), sementara dua
tempat lain menulis `hasnurgroup` (tanpa tanda hubung) — tidak menimbulkan
kerusakan karena yang satu cuma data contoh, tapi menunjukkan persis bagaimana
tiga salinan mulai berselisih tanpa ada yang menyadarinya.

**Yang dikerjakan:** `config('site.sosial')` menjadi satu sumber, dibaca
`footer.blade.php`, `StructuredData::organization()`, dan
`SeedAnalyticsSample`. Selisih ejaan ("hasnur-group" vs "hasnurgroup") di
data contoh analitik ikut hilang karena kini menunjuk nilai yang sama.

---

### M-3. Pita gradien merek ditulis ulang identik di 3 berkas — ✅ DIPERBAIKI

**Berkas:**
- `resources/views/posts/show.blade.php:22`
- `resources/views/components/page-hero.blade.php`
- `resources/views/partials/announcement.blade.php`

```blade
style="background:linear-gradient(90deg,#0F2747,#C9A227,#007A53,#E03633,#0F2747)"
```

String yang sama persis, karakter demi karakter, di tiga tempat. Palet warna
korporat baru saja diganti sekali di sesi sebelumnya (dari biru-kuning-hijau
logo lama ke Navy-Gold-Emerald-Merah sesuai TOR) — pekerjaan itu berarti
mengedit tiga berkas secara manual dan mengandalkan ingatan bahwa ketiganya
harus tetap sama.

**Yang dikerjakan:** `<x-brand-strip />`, komponen satu baris, dipakai di
ketiga berkas menggantikan `<div style="background:linear-gradient(...)">`
yang identik.

### M-4. Alamat surel rekrutmen ditulis 3 kali di satu berkas — ✅ DIPERBAIKI

**Berkas:** `resources/views/pages/career/our-vacancy.blade.php:23, 56, 65`

`recruitment@hasnurgroup.com` muncul tiga kali di halaman yang sama, satu di
antaranya di dalam atribut `mailto:` dengan subjek dinamis. Berbeda dari
`config('mail.contact_recipient')` yang sudah benar dikonfigurasi lewat
`.env` untuk formulir Hubungi Kami — alamat rekrutmen ini tidak
dikonfigurasi sama sekali.

**Yang dikerjakan:** `config('mail.recruitment_recipient')`, mengikuti pola
persis `contact_recipient` yang sudah ada, diatur lewat
`MAIL_RECRUITMENT_RECIPIENT` di `.env`. Ketiga kemunculan di
`our-vacancy.blade.php` diganti membacanya.

---

## 🟡 Rendah — sah sebagai keputusan desain, dicatat untuk kelengkapan

### L-1. Alamat kantor Cipta Daya Inovasi & Hulu Talent ditulis di teks naratif

**Berkas:** `resources/views/pages/our-business/education.blade.php:54`

Alamat dua kantor ditulis di tengah paragraf deskriptif, bukan di tabel
`offices` yang sudah dipakai untuk kantor pusat dan cabang di footer. Ini bisa
jadi memang sengaja — kedua kantor itu anak usaha di bidang pendidikan, bukan
kantor perwakilan Hasnur Group sendiri, jadi wajar tidak masuk tabel `offices`
yang skemanya untuk entitas induk. Dicatat sebagai perhatian, bukan cacat.

### L-2. Alamat kantor pusat di Kebijakan Privasi tidak menunjuk tabel `offices`

**Berkas:** `resources/views/pages/legal/kebijakan-privasi.blade.php:33-34`

"Office 8 Building Lantai 7, Jl. Senopati No. 8B, Jakarta Selatan" ditulis
manual, padahal `Office::headquarters()` kemungkinan sudah menyimpan alamat
yang sama untuk footer. Berisiko dua sumber kebenaran untuk satu alamat.
Perlu diperiksa apakah alamat di `offices` memang kantor yang sama; kalau ya,
gunakan `{{ $headOffice->address }}` di sini juga.

### L-3. Warna 7 badge Nilai Inti bukan bagian dari palet token korporat

Warna-warna di H-2 (`#DA272C`, `#83C034`, dst.) berbeda dari token
`--color-primary` / `--color-accent` / `--color-emerald` yang dipakai di
seluruh situs lain. Ini tampaknya sengaja — tiap nilai inti memang perlu warna
badge sendiri yang berbeda-beda — tapi berarti mengganti "warna aksen situs"
secara global tidak akan menyentuh bagian ini sama sekali, dan sebaliknya.

---

## ✅ Sudah benar — pola yang seharusnya ditiru untuk item di atas

Untuk perbandingan, ini bagian yang **tidak** hardcode dan menunjukkan pola
yang benar:

| Yang seharusnya rapuh | Kenapa aman |
|---|---|
| Alamat kantor pusat & cabang di footer | `Office::headquarters()`, `Office::representative()` dari tabel |
| Kartu 7 unit bisnis di beranda | `BusinessUnit::ordered()->get()` dari tabel |
| Alamat surel formulir Hubungi Kami | `config('mail.contact_recipient')`, diatur lewat `.env` |
| Masa simpan pesan kontak | `config('site.retensi_pesan_bulan')`, dipakai scope model **dan** seharusnya dipakai juga di H-1 |
| Tautan Dukungan & Kebijakan Privasi di footer panel admin | `config('site.panel.dukungan')` / `->privasi`, diatur lewat `.env` |
| Palet warna situs | Token CSS `@theme` terpusat di `app.css`, satu tempat |

M-1 s/d M-4 bisa memakai pola yang sama persis dengan kolom "Sudah benar" —
bukan solusi baru, hanya menerapkan pola yang sudah terbukti bekerja di bagian
lain aplikasi yang sama.

---

## Ringkasan tindakan

| ID | Item | Pemilik | Blokir tayang? |
|---|---|---|---|
| H-1 | Kebijakan Privasi 4 placeholder | Manajemen + Legal | **Ya** |
| H-2 | Nilai Inti hanya di kode | Produk (keputusan: CMS atau tetap) | Tidak |
| M-1 | Menu navigasi 7 unit bisnis manual | ✅ Selesai | Tidak |
| M-2 | Sosial media di 3 tempat | ✅ Selesai | Tidak |
| M-3 | Pita gradien di 3 berkas | ✅ Selesai | Tidak |
| M-4 | Surel rekrutmen 3× di 1 berkas | ✅ Selesai | Tidak |
| L-1 | Alamat 2 kantor anak usaha di teks | Tinjau saja | Tidak |
| L-2 | Alamat kantor pusat di Kebijakan Privasi | Developer | Tidak |
| L-3 | Warna badge Nilai Inti di luar token | Tinjau saja | Tidak |

Satu-satunya yang **memblokir tayang publik** adalah H-1 — dan itu sudah
ditandai sendiri di dalam kode sebagai hal yang harus selesai sebelum go-live.
Semua item M dapat dikerjakan sekaligus dalam satu sesi karena polanya identik
dengan bagian yang sudah benar di aplikasi yang sama.
