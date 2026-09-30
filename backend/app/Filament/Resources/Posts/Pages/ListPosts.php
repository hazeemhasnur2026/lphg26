<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    /**
     * Kategori, dan sekarang juga status, diambil dari URL, bukan dari
     * mekanisme tableFilters bawaan Filament.
     *
     * Filament 4 tidak mengisi $tableFilters dari query string saat halaman
     * dimuat — normalizeTableFilterValuesFromQueryString() hanya menormalisasi
     * nilai yang sudah ada di properti itu, tidak pernah mengisinya dari URL.
     * Kartu dasbor yang menaut ke
     * "/admin/posts?tableFilters[status][values][0]=in_review" karena itu
     * mendarat di daftar tak tersaring: URL-nya benar, tapi Livewire tidak
     * pernah membacanya. Ditemukan lewat pengujian klik sungguhan, bukan
     * lewat pembacaan kode — tabelnya tetap menampilkan 10 baris campuran
     * tanpa galat apa pun.
     *
     * Parameter query sendiri, dibaca dan diterapkan manual di sini,
     * menghindari mekanisme yang ternyata tidak bekerja sama sekali.
     */
    public function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if ($this->category()) {
            $query->where('category', $this->category());
        }

        if ($status = $this->statusDariUrl()) {
            $query->where('status', $status);
        }

        if (request()->query('terjadwal') === '1') {
            $query->where('status', PostStatus::Published)
                ->whereNotNull('published_at')
                ->where('published_at', '>', now());
        }

        if (request()->query('tanpa_tanggal') === '1') {
            $query->whereNull('published_at');
        }

        return $query;
    }

    private function statusDariUrl(): ?PostStatus
    {
        $nilai = request()->query('status');

        return is_string($nilai) ? PostStatus::tryFrom($nilai) : null;
    }

    public function getTitle(): string
    {
        return $this->category()
            ? config("site.listings.{$this->category()}.label")
            : parent::getTitle();
    }

    private function category(): ?string
    {
        $category = request()->query('category');

        return is_string($category) && config("site.listings.{$category}") ? $category : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => PostResource::getUrl('create', array_filter([
                    'category' => $this->category(),
                ]))),
        ];
    }
}
