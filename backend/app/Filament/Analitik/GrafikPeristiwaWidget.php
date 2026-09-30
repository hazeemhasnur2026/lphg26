<?php

namespace App\Filament\Analitik;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Peristiwa per hari, satu garis per jenis peristiwa.
 *
 * Garis, bukan batang bertumpuk. Batang bertumpuk menggambar satu tinggi
 * gabungan, dan tinggi itu menyiratkan sebuah jumlah yang berarti — padahal
 * menjumlahkan satu pencarian dengan satu unduhan tidak menghasilkan apa pun
 * yang bisa ditanyakan orang. Yang ingin dilihat di sini adalah masing-masing
 * peristiwa berubah bagaimana dari hari ke hari, dan itu pekerjaan garis.
 */
class GrafikPeristiwaWidget extends ChartWidget
{
    /**
     * Warna per jenis peristiwa, urutannya mengikuti AnalyticsEvent::PERISTIWA.
     *
     * Satu himpunan untuk mode terang dan gelap sekaligus. Chart.js melukis ke
     * kanvas, dan kanvas tidak mengenal custom property CSS — warnanya harus
     * berupa hex yang sudah pasti saat halaman dirender, sedangkan tema bisa
     * diganti sesudahnya tanpa memuat ulang. Lima langkah di bawah ini dipilih
     * karena lolos seluruh pemeriksaan pada kedua permukaan: pita terang,
     * ambang chroma, keterpisahan bagi mata yang tidak membedakan merah-hijau,
     * dan kontras terhadap latar.
     *
     * Identitas garis tidak pernah bergantung pada warna semata: legenda selalu
     * tampil, dan tabel peringkat di bawahnya memuat angka yang sama dalam
     * bentuk yang bisa dibaca tanpa melihat warna sama sekali.
     */
    private const WARNA = ['#3987e5', '#d95926', '#199e70', '#c98500', '#d55181'];

    protected ?string $heading = 'Peristiwa per hari';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '320px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 hari',
            '30' => '30 hari',
            '90' => '90 hari',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $hari = max(1, (int) $this->filter);

        $tanggal = collect(range($hari - 1, 0))
            ->map(fn (int $i) => Carbon::today()->subDays($i)->toDateString());

        // Satu kueri untuk seluruh garis. Lima kueri terpisah — satu per jenis
        // peristiwa — menanyakan hal yang sama lima kali dan menempuh indeks
        // yang sama lima kali.
        $jumlah = AnalyticsEvent::query()
            ->selectRaw('name, DATE(created_at) as tanggal, count(*) as jumlah')
            ->sejak($hari)
            ->groupBy('name', 'tanggal')
            ->get()
            ->groupBy('name')
            ->map(fn ($baris) => $baris->pluck('jumlah', 'tanggal'));

        $datasets = [];
        $i = 0;

        foreach (AnalyticsEvent::PERISTIWA as $nama => $label) {
            $perTanggal = $jumlah->get($nama) ?? collect();
            $warna = self::WARNA[$i % count(self::WARNA)];
            $i++;

            $datasets[] = [
                'label' => $label,
                // Hari tanpa peristiwa tidak punya baris di basis data. Diisi
                // nol di sini, bukan dilewati: garis yang melompati hari kosong
                // menghubungkan dua titik berjarak seminggu seolah keduanya
                // bersebelahan.
                'data' => $tanggal->map(fn (string $t) => (int) ($perTanggal[$t] ?? 0))->all(),
                'borderColor' => $warna,
                'backgroundColor' => $warna,
                'borderWidth' => 2,
                'tension' => 0,
                // Titik disembunyikan sampai disentuh. Pada rentang 90 hari,
                // titik yang selalu tampak berubah menjadi pita tebal yang
                // menutupi bentuk garisnya sendiri.
                'pointRadius' => 0,
                'pointHoverRadius' => 4,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $tanggal->map(fn (string $t) => Carbon::parse($t)->translatedFormat('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                // Legenda selalu tampil. Dengan lima garis, mematikannya berarti
                // grafik yang hanya bisa dibaca oleh yang sudah tahu isinya.
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'boxHeight' => 8],
                ],
                // Satu tooltip memuat seluruh garis pada hari yang sama.
                // Perbandingan antar peristiwa adalah pertanyaan yang paling
                // sering dibawa orang ke grafik ini, dan tooltip per garis
                // memaksanya menyentuh lima kali untuk satu hari.
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    // Jumlah peristiwa selalu bulat. Tanpa ini sumbu bisa
                    // menuliskan "0,5 unduhan" saat angkanya masih kecil.
                    'ticks' => ['precision' => 0],
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['autoSkip' => true, 'maxTicksLimit' => 10],
                ],
            ],
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'maintainAspectRatio' => false,
        ];
    }
}
