<?php

namespace App\Filament\Pages;

use App\Filament\Analitik\GrafikPeristiwaWidget;
use App\Filament\Analitik\PeringkatWidget;
use App\Filament\Analitik\RingkasanPeristiwaWidget;
use App\Models\AnalyticsEvent;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Papan analitik (CMS-10).
 *
 * Angka di halaman ini dihitung oleh situs ini sendiri, dari tabel
 * analytics_events. Ia ada karena peristiwa yang diminta TOR — CTA, unduhan,
 * pencarian, keterlibatan isi — sudah lama terkirim ke dataLayer, tapi dataLayer
 * tanpa platform yang membacanya berarti tidak ada satu pun angka yang bisa
 * dilihat siapa pun, dan platformnya menunggu keputusan di luar kode.
 *
 * Yang dijawab halaman ini: tombol mana yang diklik, berkas mana yang diunduh,
 * kata apa yang dicari orang yang tidak menemukan jalannya, artikel mana yang
 * dibaca sampai habis. Yang tidak dijawabnya: dari mana pengunjung datang,
 * kampanye mana yang bekerja, dan perbandingan dengan situs lain. Tiga
 * pertanyaan terakhir memang pekerjaan platform analitik, dan halaman ini tidak
 * berpura-pura menggantikannya.
 */
class Analitik extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Analitik';

    protected string $view = 'filament.pages.analitik';

    /**
     * Yang boleh membacanya sama dengan yang boleh membaca jejak audit.
     *
     * Kontributor tidak masuk. Papan ini memuat kata kunci yang diketik
     * pengunjung, dan pekerjaan kontributor adalah menulis naskahnya sendiri.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->canApproveContent() ?? false;
    }

    /**
     * Seluruh widget ditaruh di bawah, bukan di atas.
     *
     * Bagian atas halaman dipakai catatan tentang asal angkanya. Papan analitik
     * yang tidak menyebut dari mana angkanya datang akan dibaca sebagai angka
     * dari Google Analytics oleh siapa pun yang belum pernah membaca TOR-nya,
     * dan salah paham itu baru ketahuan saat angkanya dipakai dalam rapat.
     */
    protected function getFooterWidgets(): array
    {
        return [
            RingkasanPeristiwaWidget::class,
            GrafikPeristiwaWidget::class,
            ...array_map(
                fn (string $peristiwa) => PeringkatWidget::make(['peristiwa' => $peristiwa]),
                array_keys(AnalyticsEvent::PERISTIWA),
            ),
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        // Dua kolom untuk tabel peringkat. Kartu ringkasan dan grafik mengambil
        // lebar penuh lewat columnSpan-nya masing-masing.
        return ['sm' => 1, 'lg' => 2];
    }

    /** Platform analitik luar yang sudah dipilih, bila ada. */
    public function platformLuar(): ?string
    {
        return match (true) {
            filled(config('services.analytics.gtm_id')) => 'Google Tag Manager',
            filled(config('services.analytics.ga_id')) => 'Google Analytics 4',
            default => null,
        };
    }

    public function pencatatanMenyala(): bool
    {
        return (bool) config('services.analytics.internal');
    }

    public function masaSimpanBulan(): int
    {
        return (int) config('site.retensi_analitik_bulan');
    }

    /**
     * Berapa baris di papan ini yang berasal dari perintah analitik:contoh.
     *
     * Ditanyakan tiap kali halaman dibuka, dan jawabannya nol pada situs yang
     * berjalan normal. Satu kueri hitung pada kolom bertanda lebih murah
     * daripada satu kesalahpahaman: papan yang memuat data contoh tanpa
     * mengatakannya akan dibaca sebagai perilaku pengunjung sungguhan.
     */
    public function jumlahContoh(): int
    {
        return AnalyticsEvent::contoh()->count();
    }
}
