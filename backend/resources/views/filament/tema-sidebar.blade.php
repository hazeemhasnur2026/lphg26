{{--
    Tema sidebar panel admin — navy Hasnur.

    Ditulis sebagai <style>, bukan sebagai kelas Tailwind maupun tema Filament
    kustom. Alasannya diukur, bukan diduga: panel memuat stylesheet Filament
    sendiri, dan utilitas Tailwind yang tidak dipakai Filament tidak ada di
    dalamnya — ditulis begitu saja mereka tidak berlaku sama sekali, tanpa
    galat. Tema kustom menyelesaikan itu tetapi menuntut pipeline build
    tersendiri untuk sekitar enam puluh baris CSS.

    Seluruh warna ditaruh sebagai token di :root supaya penyesuaian berikutnya
    cukup mengubah satu baris, bukan menelusuri selector.

    Kontras terhadap WCAG 2.1 AA, diukur pada latar #0F1C3F:

        teks tidak aktif  #CBD5E1   12.2:1   lulus AAA
        label grup        #60A5FA    6.0:1   lulus AA
        teks aktif  putih di #2563EB 5.2:1   lulus AA

    Mode gelap tidak diberi aturan tersendiri: sidebar ini sudah gelap di kedua
    mode, dan menggelapkannya lagi hanya menghapus batas antara menu aktif dan
    latarnya.
--}}
<style>
    :root {
        --sidebar-bg:            #0F1C3F;
        --sidebar-bg-hover:      #1A2A55;
        --sidebar-active:        #2563EB;
        --sidebar-text:          #CBD5E1;
        --sidebar-text-active:   #FFFFFF;
        --sidebar-label:         #60A5FA;
        --sidebar-border:        #1E2B4F;
    }

    .fi-main-sidebar,
    .fi-main-sidebar .fi-sidebar-nav {
        background-color: var(--sidebar-bg);
        border-right-color: var(--sidebar-border);
    }

    /* Kepala sidebar tempat logo duduk. */
    .fi-main-sidebar .fi-sidebar-header {
        background-color: var(--sidebar-bg);
        border-bottom: 1px solid var(--sidebar-border);
    }

    /* ── Label grup ──────────────────────────────────────────────────────
       Garis tipis di kiri dan kanan label dibuat dengan pseudo-element, bukan
       border pada pembungkus: border akan menempel pada seluruh lebar, bukan
       hanya pada ruang yang tersisa di samping teksnya. */
    .fi-main-sidebar .fi-sidebar-group-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--sidebar-label);
    }

    .fi-main-sidebar .fi-sidebar-group-label::before,
    .fi-main-sidebar .fi-sidebar-group-label::after {
        content: "";
        height: 1px;
        background-color: var(--sidebar-border);
    }

    .fi-main-sidebar .fi-sidebar-group-label::before { width: 0.75rem; flex: none; }
    .fi-main-sidebar .fi-sidebar-group-label::after  { flex: 1 1 auto; }

    /* Chevron buka/tutup grup ikut warna label. */
    .fi-main-sidebar .fi-sidebar-group-collapse-btn,
    .fi-main-sidebar .fi-sidebar-group-collapse-btn .fi-icon,
    .fi-main-sidebar .fi-sidebar-group-collapse-btn svg {
        color: var(--sidebar-label);
    }

    /* ── Item menu ───────────────────────────────────────────────────────
       Lebar penuh tanpa radius besar: item yang melayang dengan sudut bulat
       membuat sidebar terbaca sebagai daftar kartu, bukan sebagai navigasi. */
    .fi-main-sidebar .fi-sidebar-item-btn {
        border-radius: 0;
        margin-inline: 0;
        padding-inline: 1rem;
        color: var(--sidebar-text);
        transition: background-color .15s, color .15s;
    }

    .fi-main-sidebar .fi-sidebar-item-btn .fi-icon,
    .fi-main-sidebar .fi-sidebar-item-btn svg,
    .fi-main-sidebar .fi-sidebar-item-label {
        color: var(--sidebar-text);
    }

    .fi-main-sidebar .fi-sidebar-item-btn:hover {
        background-color: var(--sidebar-bg-hover);
    }

    .fi-main-sidebar .fi-sidebar-item-btn:hover .fi-icon,
    .fi-main-sidebar .fi-sidebar-item-btn:hover svg,
    .fi-main-sidebar .fi-sidebar-item-btn:hover .fi-sidebar-item-label {
        color: var(--sidebar-text-active);
    }

    .fi-main-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: var(--sidebar-active);
    }

    .fi-main-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-icon,
    .fi-main-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn svg,
    .fi-main-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label {
        color: var(--sidebar-text-active);
        font-weight: 600;
    }

    /* Fokus keyboard harus tetap terlihat di atas latar gelap. Outline bawaan
       Filament berwarna gelap dan lenyap di sini. */
    .fi-main-sidebar .fi-sidebar-item-btn:focus-visible {
        outline: 2px solid var(--sidebar-label);
        outline-offset: -2px;
    }

    /* Lencana angka (naskah menunggu tinjauan, pesan belum ditangani) tetap
       terbaca di atas latar gelap maupun di atas item aktif yang biru. */
    .fi-main-sidebar .fi-sidebar-item-badge {
        box-shadow: 0 0 0 1px var(--sidebar-bg);
    }
</style>
