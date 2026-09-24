<?php

namespace App\Filament\Resources\SlideBanners\Pages;

use App\Filament\Resources\SlideBanners\SlideBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlideBanners extends ListRecords
{
    protected static string $resource = SlideBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
