<?php

namespace App\Filament\Analitik;

use App\Models\AnalyticsEvent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Sepuluh label teratas untuk satu jenis peristiwa.
 *
 * Satu kelas untuk kelima peristiwa, dipasang lima kali dengan properti
 * berbeda di halaman Analitik. Lima kelas yang isinya sama kecuali satu nama
 * kolom dan satu judul akan menyimpan lima salinan kueri yang sama, dan
 * perbaikan pada satu di antaranya tidak pernah ikut ke empat lainnya.
 *
 * Tabel ini sekaligus bentuk terbaca dari grafik di atasnya: angkanya sama,
 * tapi tidak perlu membedakan lima warna untuk membacanya.
 */
class PeringkatWidget extends TableWidget
{
    /** Nama peristiwa, salah satu kunci AnalyticsEvent::PERISTIWA. */
    public string $peristiwa = 'cta_click';

    /** Rentang hari yang diperingkat. */
    public int $hari = 30;

    /** Berapa baris teratas yang ditampilkan. */
    public int $baris = 10;

    /**
     * Judul per peristiwa.
     *
     * Ditulis terpisah dari AnalyticsEvent::PERISTIWA karena menjawab
     * pertanyaan yang berbeda. Yang di model adalah nama peristiwanya; yang di
     * sini adalah pertanyaan yang dijawab tabelnya.
     */
    private const JUDUL = [
        'cta_click' => 'CTA paling sering diklik',
        'file_download' => 'Berkas paling sering diunduh',
        'search' => 'Kata kunci paling sering dicari',
        'content_read' => 'Artikel paling sering dibaca tuntas',
        'outbound_click' => 'Tautan keluar paling sering diklik',
    ];

    /** Nama kolom label, mengikuti isi label pada peristiwa itu. */
    private const KOLOM = [
        'cta_click' => 'Nama CTA',
        'file_download' => 'Berkas',
        'search' => 'Kata kunci',
        'content_read' => 'Artikel',
        'outbound_click' => 'Tujuan',
    ];

    public function getTableHeading(): string
    {
        return self::JUDUL[$this->peristiwa] ?? AnalyticsEvent::PERISTIWA[$this->peristiwa] ?? $this->peristiwa;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AnalyticsEvent::query()
                    // min(id) hanya untuk memberi tiap baris hasil sebuah kunci.
                    // Tabel Filament mengidentifikasi barisnya lewat kunci utama,
                    // dan hasil pengelompokan tidak punya satu pun.
                    ->selectRaw('min(id) as id, label, count(*) as jumlah, count(distinct visitor) as pengunjung')
                    ->where('name', $this->peristiwa)
                    ->sejak($this->hari)
                    // Peristiwa tanpa label tidak bisa diperingkat menurut apa
                    // pun. Barisnya tetap terhitung di kartu ringkasan dan di
                    // grafik; yang tidak masuk hanya peringkat ini.
                    ->whereNotNull('label')
                    ->where('label', '!=', '')
                    ->groupBy('label')
                    ->orderByDesc('jumlah')
                    ->limit($this->baris)
            )
            ->columns([
                TextColumn::make('label')
                    ->label(self::KOLOM[$this->peristiwa] ?? 'Label')
                    ->wrap(),

                TextColumn::make('jumlah')
                    ->label('Peristiwa')
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('pengunjung')
                    ->label('Pengunjung')
                    ->numeric()
                    ->alignEnd()
                    // Bedanya jumlah dan pengunjung adalah bedanya "diklik
                    // sepuluh kali" dengan "diklik sepuluh orang", dan hanya
                    // yang kedua berarti sesuatu tentang ketertarikan.
                    ->tooltip('Pengunjung berbeda yang memicunya'),
            ])
            // Pemeringkatan sudah dikerjakan kueri, dan urutan itu yang menjadi
            // isi tabel. Paginasi pada sepuluh baris hanya menambah kontrol yang
            // tidak menuju ke mana-mana.
            ->paginated(false)
            ->emptyStateHeading('Belum ada yang tercatat')
            ->emptyStateDescription("Rentang {$this->hari} hari terakhir.");
    }
}
