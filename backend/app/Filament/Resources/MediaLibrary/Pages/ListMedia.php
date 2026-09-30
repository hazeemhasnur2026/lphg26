<?php

namespace App\Filament\Resources\MediaLibrary\Pages;

use App\Filament\Resources\MediaLibrary\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    /**
     * "Belum ada alt text" datang dari kartu dasbor lewat parameter query,
     * bukan lewat tableFilters bawaan Filament.
     *
     * Filament 4 tidak mengisi $tableFilters dari URL saat halaman dimuat, jadi
     * kartu yang menaut ke "?tableFilters[tanpa_alt][isActive]=1" mendarat di
     * daftar tak tersaring tanpa galat apa pun.
     */
    public function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if (request()->query('tanpa_alt') === '1') {
            $query->where('mime_type', 'like', 'image/%')
                ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''));
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Unggah berkas')];
    }
}
