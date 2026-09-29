<?php

namespace App\Filament\Widgets;

use App\Enums\PostStatus;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Angka yang menjawab "apa yang perlu saya kerjakan hari ini".
 *
 * Bukan "berapa banyak yang kita punya". Jumlah total artikel tidak pernah
 * mengubah keputusan siapa pun; naskah yang menunggu ditinjau sejak kemarin
 * mengubahnya.
 */
class RingkasanKontenWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        $menunggu = Post::awaitingReview()->count();
        $draf = Post::where('status', PostStatus::Draft)->count();
        $terjadwal = Post::where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now())
            ->count();
        $tanpaTanggal = Post::undated()->count();

        $stats = [
            Stat::make('Menunggu tinjauan', $menunggu)
                ->description($menunggu > 0 ? 'Ada naskah yang menunggu dibaca' : 'Tidak ada antrean')
                ->color($menunggu > 0 ? 'warning' : 'success'),

            Stat::make('Draf', $draf)
                ->description('Belum diajukan')
                ->color('gray'),

            Stat::make('Terjadwal', $terjadwal)
                ->description($terjadwal > 0 ? 'Akan terbit sendiri' : 'Tidak ada jadwal')
                ->color('info'),

            Stat::make('Tanpa tanggal', $tanpaTanggal)
                ->description('Artikel migrasi yang tanggalnya belum dipastikan')
                ->color($tanpaTanggal > 0 ? 'warning' : 'success'),
        ];

        // Gambar tanpa alt text adalah hutang aksesibilitas. Ditampilkan hanya
        // bila memang ada, supaya papan ini tidak penuh angka nol.
        $tanpaAlt = Media::where('mime_type', 'like', 'image/%')
            ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''))
            ->count();

        if ($tanpaAlt > 0) {
            $stats[] = Stat::make('Gambar tanpa alt text', $tanpaAlt)
                ->description('Perlu dilengkapi untuk pembaca layar')
                ->color('warning');
        }

        if ($user?->canReadContactMessages()) {
            $pesan = ContactMessage::whereNull('handled_at')->count();

            $stats[] = Stat::make('Pesan belum ditangani', $pesan)
                ->description('Dari formulir Hubungi Kami')
                ->color($pesan > 0 ? 'warning' : 'gray');
        }

        return $stats;
    }
}
