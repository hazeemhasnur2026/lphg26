<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_url')
                    ->label('Sampul')
                    ->height(40),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => config("site.listings.{$state}.label") ?? $state)
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('tanpa tanggal'),

                IconColumn::make('external_url')
                    ->label('Liputan media')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->source_name),
            ])
            // Sama dengan urutan di beranda dan halaman daftar: artikel tanpa
            // tanggal tidak tenggelam diam-diam ke dasar.
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn () => collect(config('site.listings'))->map(fn ($l) => $l['label'])->all()),

                TernaryFilter::make('published_at')
                    ->label('Tanggal terbit')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Sudah bertanggal')
                    ->falseLabel('Tanpa tanggal'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
