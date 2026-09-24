<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('email')->label('Surel')->searchable()->copyable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (UserRole $state) => $state === UserRole::Administrator ? 'primary' : 'gray'),
                TextColumn::make('created_at')->label('Dibuat')->date('d M Y')->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->options(collect(UserRole::cases())->mapWithKeys(
                        fn (UserRole $peran) => [$peran->value => $peran->getLabel()]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                // Akun sendiri tidak bisa dihapus dari sini: kehilangan
                // administrator terakhir berarti tidak ada yang bisa memulihkan.
                DeleteAction::make()->visible(fn ($record) => $record->isNot(auth()->user())),
            ]);
    }
}
