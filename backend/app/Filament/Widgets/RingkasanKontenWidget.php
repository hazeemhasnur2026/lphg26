<?php

namespace App\Filament\Widgets;

use App\Enums\PostStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\MediaLibrary\MediaResource;
use App\Filament\Resources\Posts\PostResource;
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
 *
 * Tiap angka menautkan ke daftar yang sudah tersaring. Angka yang menunjukkan
 * ada tujuh pekerjaan tetapi tidak memberi tahu yang mana hanya memindahkan
 * pencarian ke orangnya.
 */
class RingkasanKontenWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * URL daftar artikel dengan parameter query yang dibaca manual oleh
     * ListPosts::getTableQuery().
     *
     * Bentuk sebelumnya menaut lewat tableFilters[...] milik Filament, dan itu
     * tidak pernah bekerja: Filament 4 tidak mengisi properti $tableFilters
     * dari query string saat halaman dimuat —
     * normalizeTableFilterValuesFromQueryString() hanya menormalisasi nilai
     * yang sudah ada di properti itu, tidak pernah mengisinya dari URL. Kartu
     * "Menunggu tinjauan" mendarat di daftar tak tersaring, sepuluh baris
     * campuran tanpa galat apa pun. Ditemukan lewat klik sungguhan di panel,
     * bukan lewat pembacaan kode.
     *
     * Parameter query polos di sini cocok dengan yang dibaca manual di
     * getTableQuery() pada masing-masing halaman daftar.
     */
    private function daftarArtikel(array $query): string
    {
        return PostResource::getUrl('index', $query);
    }

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
                ->color($menunggu > 0 ? 'warning' : 'success')
                ->url($this->daftarArtikel(['status' => PostStatus::InReview->value])),

            Stat::make('Draf', $draf)
                ->description('Belum diajukan')
                ->color('gray')
                ->url($this->daftarArtikel(['status' => PostStatus::Draft->value])),

            Stat::make('Terjadwal', $terjadwal)
                ->description($terjadwal > 0 ? 'Akan terbit sendiri' : 'Tidak ada jadwal')
                ->color('info')
                ->url($this->daftarArtikel(['terjadwal' => '1'])),

            Stat::make('Tanpa tanggal', $tanpaTanggal)
                ->description('Artikel migrasi yang tanggalnya belum dipastikan')
                ->color($tanpaTanggal > 0 ? 'warning' : 'success')
                ->url($this->daftarArtikel(['tanpa_tanggal' => '1'])),
        ];

        // Gambar tanpa alt text adalah hutang aksesibilitas. Ditampilkan hanya
        // bila memang ada, supaya papan ini tidak penuh angka nol.
        $tanpaAlt = Media::where('mime_type', 'like', 'image/%')
            ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''))
            ->count();

        if ($tanpaAlt > 0) {
            $stats[] = Stat::make('Gambar tanpa alt text', $tanpaAlt)
                ->description('Perlu dilengkapi untuk pembaca layar')
                ->color('warning')
                ->url(MediaResource::getUrl('index', ['tanpa_alt' => '1']));
        }

        if ($user?->canReadContactMessages()) {
            $pesan = ContactMessage::whereNull('handled_at')->count();

            $stats[] = Stat::make('Pesan belum ditangani', $pesan)
                ->description('Dari formulir Hubungi Kami')
                ->color($pesan > 0 ? 'warning' : 'gray')
                ->url(ContactMessageResource::getUrl('index', ['belum_ditangani' => '1']));
        }

        return $stats;
    }
}
