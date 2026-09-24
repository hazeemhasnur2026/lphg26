<?php

namespace App\Filament\Resources\Vacancies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Posisi')->searchable()->wrap(),
                TextColumn::make('department')->label('Unit')->placeholder('-'),
                TextColumn::make('location')->label('Lokasi')->placeholder('-')->color('gray'),
                TextColumn::make('deadline')
                    ->label('Tutup')
                    ->date('d M Y')
                    ->placeholder('tanpa tenggat')
                    ->sortable(),
                ToggleColumn::make('is_open')->label('Dibuka'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_open')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Sedang dibuka')
                    ->falseLabel('Ditutup'),
            ])
            ->emptyStateHeading('Belum ada lowongan')
            // Halaman publiknya sudah menyiapkan daftar dan menyatakan
            // keadaannya saat kosong, jadi tabel kosong di sini bukan kesalahan.
            ->emptyStateDescription('Halaman Lowongan Kami menampilkan keterangan "belum ada posisi yang dibuka" selama tabel ini kosong.')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
