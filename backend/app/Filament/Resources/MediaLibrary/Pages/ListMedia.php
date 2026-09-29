<?php

namespace App\Filament\Resources\MediaLibrary\Pages;

use App\Filament\Resources\MediaLibrary\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Unggah berkas')];
    }
}
