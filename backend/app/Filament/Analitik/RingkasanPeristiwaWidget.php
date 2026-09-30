<?php

namespace App\Filament\Analitik;

use App\Models\AnalyticsEvent;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Jumlah tiap peristiwa selama tiga puluh hari terakhir.
 *
 * Berada di luar app/Filament/Widgets dengan sengaja. Direktori itu ditemukan
 * sendiri oleh discoverWidgets() di AdminPanelProvider, dan tiap widget yang
 * ditemukan di sana ikut muncul di Dashboard. Widget di berkas ini hanya
 * dipanggil dari halaman Analitik.
 */
class RingkasanPeristiwaWidget extends StatsOverviewWidget
{
    /** Rentang yang diringkas kartu-kartu ini. */
    private const HARI = 30;

    /** Panjang grafik kecil di dalam tiap kartu. */
    private const HARI_GRAFIK = 7;

    protected ?string $heading = 'Tiga puluh hari terakhir';

    protected function getStats(): array
    {
        // Satu kueri untuk seluruh kartu, dikelompokkan per peristiwa per hari.
        // Bentuk ini sekaligus melayani tiga angka yang dibutuhkan tiap kartu —
        // jumlah rentang, jumlah hari ini, dan grafik harian — sehingga tidak
        // ada kueri yang berulang lima kali karena kebetulan ada lima kartu.
        $harian = AnalyticsEvent::query()
            ->selectRaw('name, DATE(created_at) as tanggal, count(*) as jumlah')
            ->sejak(self::HARI)
            ->groupBy('name', 'tanggal')
            ->get()
            ->groupBy('name');

        // Pengunjung unik tidak bisa dijumlahkan dari baris harian: orang yang
        // datang tiga hari berturut-turut terhitung tiga kali. Jadi satu kueri
        // terpisah, bukan penjumlahan yang kelihatan benar tapi selalu lebih
        // besar dari kenyataan.
        $pengunjung = AnalyticsEvent::query()
            ->selectRaw('name, count(distinct visitor) as pengunjung')
            ->sejak(self::HARI)
            ->groupBy('name')
            ->pluck('pengunjung', 'name');

        $hariIni = Carbon::today()->toDateString();

        $stats = [];

        foreach (AnalyticsEvent::PERISTIWA as $nama => $label) {
            $baris = $harian->get($nama) ?? collect();
            $perTanggal = $baris->pluck('jumlah', 'tanggal');

            $total = (int) $perTanggal->sum();
            $orang = (int) ($pengunjung[$nama] ?? 0);
            $tadi = (int) ($perTanggal[$hariIni] ?? 0);

            $stats[] = Stat::make($label, number_format($total, 0, ',', '.'))
                ->description($total === 0
                    ? 'Belum ada'
                    : "{$orang} pengunjung · {$tadi} hari ini")
                ->color($total > 0 ? 'primary' : 'gray')
                // Grafik kecil hanya berguna bila ada yang bisa dibandingkan.
                // Pada deret yang seluruhnya nol ia menggambar garis datar yang
                // tampak seperti data, padahal artinya tidak ada data.
                ->chart($total > 0 ? $this->deret($perTanggal) : null);
        }

        return $stats;
    }

    /**
     * Jumlah per hari untuk tujuh hari terakhir, termasuk hari yang kosong.
     *
     * Hari tanpa peristiwa tidak punya baris di basis data. Bila celahnya tidak
     * diisi nol, grafiknya merapatkan dua hari yang berjarak seminggu menjadi
     * dua titik bersebelahan, dan bentuk yang terlihat bukan bentuk yang
     * sebenarnya terjadi.
     *
     * @param  Collection<string, int>  $perTanggal
     * @return array<int, int>
     */
    private function deret($perTanggal): array
    {
        $deret = [];

        for ($i = self::HARI_GRAFIK - 1; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i)->toDateString();
            $deret[] = (int) ($perTanggal[$tanggal] ?? 0);
        }

        return $deret;
    }
}
