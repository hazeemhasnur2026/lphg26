<?php

namespace App\Filament\Resources\MediaLibrary;

use App\Filament\Resources\MediaLibrary\Pages\CreateMedia;
use App\Filament\Resources\MediaLibrary\Pages\EditMedia;
use App\Filament\Resources\MediaLibrary\Pages\ListMedia;
use App\Filament\Resources\MediaLibrary\Schemas\MediaForm;
use App\Filament\Resources\MediaLibrary\Tables\MediaTable;
use App\Models\Media;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Pustaka';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Berkas';

    protected static ?string $pluralModelLabel = 'Pustaka Media';

    protected static ?string $recordTitleAttribute = 'original_name';

    /**
     * Slug ditulis sendiri.
     *
     * Filament menurunkannya dari namespace resource, dan kelas ini duduk di
     * MediaLibrary\MediaResource karena "Media" sendirian bentrok dengan model
     * dan tabelnya. Hasil turunannya /admin/media-library/media — dua kata yang
     * sama berturut-turut, dan alamat yang tidak bisa ditebak orang yang
     * melihat menunya tertulis "Pustaka Media".
     */
    protected static ?string $slug = 'media';

    public static function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
