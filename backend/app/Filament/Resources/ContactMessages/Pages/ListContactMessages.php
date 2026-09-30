<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    /**
     * "Belum ditangani" datang dari kartu dasbor lewat parameter query, bukan
     * lewat tableFilters bawaan Filament — lihat catatan yang sama di
     * ListPosts::getTableQuery().
     */
    public function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if (request()->query('belum_ditangani') === '1') {
            $query->whereNull('handled_at');
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
