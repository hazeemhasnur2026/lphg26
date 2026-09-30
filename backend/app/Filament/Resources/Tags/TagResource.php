<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Tag dikelola di satu halaman datar, tanpa halaman buat dan sunting sendiri.
 *
 * Isinya cuma satu kolom yang diketik orang. Halaman penuh untuk satu kolom
 * berarti dua klik tambahan tiap kali, dan tag biasanya dirapikan berturut-turut
 * dalam satu duduk.
 */
class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Pustaka';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Tag';

    protected static ?string $pluralModelLabel = 'Tag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->color('gray'),
                TextColumn::make('posts_count')
                    ->label('Artikel')
                    ->counts('posts')
                    ->sortable(),
                TextColumn::make('media_count')
                    ->label('Berkas')
                    ->counts('media')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->headerActions([CreateAction::make()->label('Tambah tag')])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTags::route('/')];
    }
}
