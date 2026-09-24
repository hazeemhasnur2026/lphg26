<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    /**
     * Kategori diambil dari URL, bukan dari filter tabel, supaya satu menu
     * "Aktifitas CSR" benar-benar membuka daftar CSR, dan tombol Buat di
     * halaman itu langsung membuat artikel CSR.
     */
    public function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        return $this->category()
            ? $query->where('category', $this->category())
            : $query;
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
