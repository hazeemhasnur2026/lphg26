import Alpine from 'alpinejs';

/*
 * Alpine di-bundle, bukan diambil dari CDN. Saat pengujian kedua CDN (Tailwind
 * dan Alpine) pernah gagal bersamaan dan halaman kehilangan seluruh gaya
 * sekaligus semua interaksi. Slider, menu, dan reveal semuanya mati.
 *
 * createCarousel() dan gallerySlider() tetap tinggal di /assets/js/main.js:
 * berkas itu dipakai bersama prototipe statis. Skrip klasik itu dieksekusi saat
 * parsing, sebelum modul ber-defer ini jalan, jadi fungsinya sudah ada di
 * window ketika Alpine mulai membaca x-data.
 */
window.Alpine = Alpine;
Alpine.start();
