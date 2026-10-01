<?php

namespace App\Filament\Resources\Offices\Tables;

use App\Models\Office;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfficesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('name->id', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")),
                TextColumn::make('region')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => $state === Office::HEADQUARTERS ? 'primary' : 'gray'),
                TextColumn::make('address')->label('Alamat')->limit(60)->wrap()->color('gray'),
                TextColumn::make('phone')->label('Telepon')->placeholder('-'),
                TextColumn::make('email')->label('Surel')->placeholder('-'),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->paginated(false)
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
