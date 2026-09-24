<?php

namespace App\Filament\Resources\SlideBanners\Pages;

use App\Filament\Resources\SlideBanners\SlideBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSlideBanner extends EditRecord
{
    protected static string $resource = SlideBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
