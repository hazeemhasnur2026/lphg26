{{--
    Footer panel admin.

    Gayanya ditulis di sini sebagai <style>, bukan sebagai kelas Tailwind.

    Panel Filament memuat stylesheet miliknya sendiri, bukan resources/css/app.css,
    dan stylesheet itu hanya memuat utilitas yang dipakai Filament. Kelas seperti
    text-center, border-t, dan gap-4 tidak ada di dalamnya: ditulis begitu saja
    mereka tidak berlaku sama sekali, tanpa galat, dan footernya tampil sebagai
    HTML polos. Diukur pada 29 September 2026 — text-align "start", border "0px",
    padding "0px".

    Jalan lain adalah mendaftarkan tema Filament kustom, yang berarti satu
    pipeline build lagi untuk sepuluh baris CSS.

    Warna mengikuti mode gelap lewat .fi-theme-dark, kelas yang dipasang Filament
    sendiri di <html>.
--}}
<style>
    .hg-footer {
        margin-top: 2rem;
        padding: 1.5rem 1rem;
        border-top: 1px solid rgb(228 228 231);
        text-align: center;
    }

    .hg-footer p {
        margin: 0;
        font-size: 0.75rem;
        line-height: 1.5;
        color: rgb(113 113 122);
    }

    .hg-footer nav {
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-size: 0.75rem;
    }

    .hg-footer a {
        color: rgb(113 113 122);
        text-decoration: none;
        transition: color 0.15s;
    }

    .hg-footer a:hover,
    .hg-footer a:focus-visible {
        color: rgb(var(--primary-600, 79 70 229));
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .hg-footer span[aria-hidden] {
        color: rgb(212 212 216);
    }

    .fi-theme-dark .hg-footer { border-top-color: rgb(255 255 255 / 0.1); }
    .fi-theme-dark .hg-footer p,
    .fi-theme-dark .hg-footer a { color: rgb(161 161 170); }
    .fi-theme-dark .hg-footer span[aria-hidden] { color: rgb(82 82 91); }
    .fi-theme-dark .hg-footer a:hover { color: rgb(var(--primary-400, 129 140 248)); }
</style>

<footer class="hg-footer">
    {{-- Tahunnya diambil dari jam, bukan ditulis tetap. Angka hak cipta yang
         ditanam di kode selalu basi persis pada 1 Januari, dan tidak ada yang
         memeriksanya sampai ada yang menyadari panelnya masih mengaku tahun
         lalu. --}}
    <p>&copy; {{ now()->year }} {{ config('site.panel.pemilik') }}</p>

    <nav aria-label="Tautan panel">
        <a href="{{ config('site.panel.dukungan') }}">Dukungan</a>

        <span aria-hidden="true">&middot;</span>

        {{-- Tab baru: kebijakan privasi ada di situs publik, dan menarik orang
             keluar dari panel berarti ia kehilangan formulir yang sedang diisi. --}}
        <a href="{{ config('site.panel.privasi') }}" target="_blank" rel="noopener">
            Kebijakan Privasi
        </a>
    </nav>
</footer>
