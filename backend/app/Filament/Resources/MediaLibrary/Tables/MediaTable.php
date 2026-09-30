<?php

namespace App\Filament\Resources\MediaLibrary\Tables;

use App\Models\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label('Pratinjau')
                    ->height(48)
                    // PDF dan video tidak punya pratinjau gambar; kolomnya
                    // dibiarkan kosong daripada menampilkan ikon rusak.
                    ->visibility(fn (Media $record) => $record->isImage()),

                TextColumn::make('original_name')
                    ->label('Berkas')
                    ->searchable()
                    ->limit(40)
                    ->description(fn (Media $record) => $record->size_for_humans),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(40),

                IconColumn::make('alt_text')
                    ->label('Alt')
                    ->boolean()
                    // Gambar tanpa alt text adalah hutang aksesibilitas, dan
                    // satu-satunya cara ia terbayar adalah kalau kelihatan.
                    ->tooltip(fn (Media $record) => $record->alt_text ?: 'Belum ada alt text')
                    ->visible(true),

                TextColumn::make('folder')
                    ->label('Folder')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('tags.name')
                    ->label('Tag')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('mime_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state) => match (true) {
                        str_starts_with($state, 'image/') => 'Gambar',
                        str_starts_with($state, 'video/') => 'Video',
                        $state === 'application/pdf' => 'PDF',
                        default => $state,
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('uploader.name')
                    ->label('Diunggah oleh')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('folder')
                    ->label('Folder')
                    ->options(fn () => Media::whereNotNull('folder')
                        ->distinct()->orderBy('folder')->pluck('folder', 'folder')->all()),

                SelectFilter::make('tags')
                    ->label('Tag')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('jenis')
                    ->label('Jenis berkas')
                    ->options([
                        'image' => 'Gambar',
                        'video' => 'Video',
                        'pdf' => 'PDF',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'image' => $query->where('mime_type', 'like', 'image/%'),
                        'video' => $query->where('mime_type', 'like', 'video/%'),
                        'pdf' => $query->where('mime_type', 'application/pdf'),
                        default => $query,
                    }),

                Filter::make('tanpa_alt')
                    ->label('Belum ada alt text')
                    ->query(fn (Builder $query) => $query
                        ->where('mime_type', 'like', 'image/%')
                        ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''))),
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
