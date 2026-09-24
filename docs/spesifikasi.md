# Spesifikasi Project: Rejuvenation Website Hasnur Group (Laravel)

## 1. Ringkasan Project

Rebuild website corporate Hasnur Group (hasnurgroup.com) menggunakan **Laravel**, dengan tujuan modernisasi tech stack dan UI/UX, **tanpa mengubah konten/isi** yang sudah ada di website saat ini.

## 2. Tech Stack

- **Backend**: Laravel (versi terbaru)
- **Frontend**: Blade + Tailwind CSS (atau Laravel + Livewire/Alpine.js untuk interaktivitas ringan)
- **Database**: MySQL — untuk konten dinamis (Publikasi, Berita, CSR Activity, Career/Lowongan)
- **Admin Panel**: Laravel-based CMS (opsional: Filament) agar tim non-teknis bisa update Publikasi/Berita/Lowongan sendiri

## 3. Struktur Halaman (Sitemap)

Struktur & isi konten mengikuti website existing — **tidak diubah**, hanya dipindahkan ke arsitektur Laravel.

### 3.1 Beranda (Home)
- Hero slider (contoh konten saat ini: "HUT ke-60 HASNUR GROUP")
- Quote founder: H. Abdussamad Sulaiman HB & Hj. Nurhayati
- Section "Tentang Kami" (ringkas + link ke halaman lengkap)
- Timeline sejarah perusahaan (1966–2023)
- Grid "Bisnis Kami" (7 SBU)
- Feed "Aktivitas Kami" (Latest Update / News / CSR — 3 kartu terbaru)
- Footer: info kantor (Jakarta, Kalimantan Selatan, Kalimantan Tengah), recent post, social media, link terkait

### 3.2 Tentang Kami
- Ikhtisar (`/page/about-us/overview`)
- Visi Misi (`/page/about-us/vision-mission`)
- Nilai Inti (`/page/about-us/core-value`)

### 3.3 Bisnis Kami (7 SBU)
- Logistik (`/page/our-business/logistic`)
- Agribisnis & Kehutanan (`/page/our-business/agribusiness-forestry`)
- Energi (`/page/our-business/energy`)
- Teknologi & Jasa (`/page/our-business/technology-services`)
- Pendidikan (`/page/our-business/education`)
- Kebutuhan Pelanggan / Consumer (`/page/our-business/consumer`)
- Investasi (`/page/our-business/investment`)

### 3.4 Tanggung Jawab Sosial Perusahaan (CSR)
- Ikhtisar (`/page/csr/csr-overview`)
- Aktifitas CSR (`/page/csr/csr-activity`)

### 3.5 Keselamatan, Kesehatan, & Lingkungan (SHE)
- Ikhtisar (`/page/she/overview-1`)
- Aktivitas SHE (`/page/she/she-activity`)

### 3.6 Karir
- Lowongan Kami (`/page/career/our-vacancy`)

### 3.7 Publikasi
- Update Terbaru (`/page/publication/latest-update`)
- Berita (`/page/publication/news`)
- Press Release (`/page/publication/press-release`)
- Multimedia (`/page/publication/multimedia`)
- Galeri (`/page/publication/gallery`)

### 3.8 Contact Us
- Kantor Pusat: Jakarta (Office 8 Building, Jl. Senopati No. 8B, Jakarta Selatan)
- Kantor Perwakilan: Kalimantan Selatan (Jl. Berangas Timur No.95 A Alalak), Kalimantan Tengah (Jl. G.Obos No.88 A Palangkaraya)

## 4. Model Data (untuk Konten Dinamis)

Konten yang butuh CRUD via admin panel:

- **Post** (Publikasi/News/CSR/Latest Update): `title`, `slug`, `category` (latest-update / news / press-release / csr), `thumbnail`, `body`, `published_at`
- **BusinessUnit** (7 SBU): `name`, `slug`, `icon/image`, `short_description`, `full_content`
- **Vacancy** (Karir): `title`, `department`, `location`, `description`, `requirements`, `deadline`
- **Office**: `name`, `address`, `phone`, `fax`, `email`, `region`
- **SlideBanner** (Hero): `image`, `title`, `subtitle`, `order`

## 5. Perbaikan yang Diusulkan (di luar konten)

Catatan: ini perbaikan teknis/UX, bukan perubahan konten.

- Perbaiki link LinkedIn di footer yang saat ini rusak (`hDps://...`)
- Timeline sejarah (1966–2023) dibuat sebagai komponen visual interaktif, bukan list statis
- Konsistensi bahasa (nav Bahasa Indonesia, beberapa berita full English) — tambahkan language switcher jika diperlukan
- Optimasi gambar (lazy loading, WebP) untuk hero slider dan grid SBU
- Responsive check ulang untuk mobile

## 6. Struktur Folder Laravel (Usulan)

```
app/
  Models/
    Post.php
    BusinessUnit.php
    Vacancy.php
    Office.php
    SlideBanner.php
  Http/Controllers/
    HomeController.php
    PageController.php
    PostController.php
    VacancyController.php
resources/
  views/
    layouts/
      app.blade.php
    home.blade.php
    pages/
      about/
      business/
      csr/
      she/
      career/
      publication/
    partials/
      header.blade.php
      footer.blade.php
routes/
  web.php
database/
  migrations/
  seeders/
    PostSeeder.php
    BusinessUnitSeeder.php
```

## 7. Langkah Selanjutnya

1. Setup project Laravel baru + Tailwind
2. Buat migration & model sesuai struktur data di atas
3. Seed data dari konten existing website (isi tidak diubah)
4. Build layout (header, footer, hero) sesuai desain baru
5. Build masing-masing halaman sesuai sitemap di atas
6. Setup admin panel untuk update Publikasi/Karir
7. Testing responsive & deploy
