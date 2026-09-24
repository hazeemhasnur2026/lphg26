<?php

namespace App\Filament\Resources\SlideBanners;

use App\Filament\Resources\SlideBanners\Pages\CreateSlideBanner;
use App\Filament\Resources\SlideBanners\Pages\EditSlideBanner;
use App\Filament\Resources\SlideBanners\Pages\ListSlideBanners;
use App\Filament\Resources\SlideBanners\Schemas\SlideBannerForm;
use App\Filament\Resources\SlideBanners\Tables\SlideBannersTable;
use App\Models\SlideBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SlideBannerResource extends Resource
{
    protected static ?string $model = SlideBanner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Situs';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Slide Beranda';

    protected static ?string $pluralModelLabel = 'Slider Beranda';

    public static function form(Schema $schema): Schema
    {
        return SlideBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlideBannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSlideBanners::route('/'),
            'create' => CreateSlideBanner::route('/create'),
            'edit' => EditSlideBanner::route('/{record}/edit'),
        ];
    }
}
