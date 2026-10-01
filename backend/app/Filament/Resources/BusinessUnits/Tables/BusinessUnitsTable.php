<?php

namespace App\Filament\Resources\BusinessUnits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BusinessUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('Foto')->height(40),
                TextColumn::make('name')
                    ->label('Nama')
                    // Kolom JSON translatable; ->searchable() bawaan tidak
                    // bisa mencocokkan isi JSON.
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('name->id', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")),
                TextColumn::make('slug')->searchable()->color('gray'),
                IconColumn::make('website_url')->label('Situs')->boolean(),
                ColorColumn::make('tint_color')->label('Latar'),
                ColorColumn::make('accent_color')->label('Aksen'),
            ])
            // Urutan kartu adalah isi, bukan preferensi tampilan: nomor urutnya
            // menentukan lebar kolom dan jeda animasi di beranda.
            ->defaultSort('order')
            ->reorderable('order')
            ->paginated(false)
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
