<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\ContentType;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\PostWorkflow;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->wrap()
                    ->description(fn (Post $record) => $record->author?->name),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('content_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => config("site.listings.{$state}.label") ?? $state)
                    ->sortable(),

                TextColumn::make('tags.name')
                    ->label('Tag')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('tanpa tanggal')
                    // Jadwal yang belum tiba ditandai, supaya tidak disangka
                    // sudah tampil di situs.
                    ->description(fn (Post $record) => $record->published_at?->isFuture() ? 'terjadwal' : null),

                IconColumn::make('external_url')
                    ->label('Liputan media')
                    ->boolean()
                    ->toggleable()
                    ->tooltip(fn ($record) => $record->source_name),
            ])
            // Sama dengan urutan di beranda dan halaman daftar: artikel tanpa
            // tanggal tidak tenggelam diam-diam ke dasar.
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PostStatus::class)
                    ->multiple(),

                SelectFilter::make('content_type')
                    ->label('Tipe konten')
                    ->options(ContentType::class)
                    ->multiple(),

                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn () => collect(config('site.listings'))->map(fn ($l) => $l['label'])->all()),

                SelectFilter::make('author')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('tags')
                    ->label('Tag')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('business_unit')
                    ->label('Unit bisnis')
                    ->relationship('businessUnit', 'name'),

                Filter::make('rentang_terbit')
                    ->schema([
                        DatePicker::make('dari')->label('Terbit dari'),
                        DatePicker::make('sampai')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn ($q, $v) => $q->whereDate('published_at', '>=', $v))
                        ->when($data['sampai'] ?? null, fn ($q, $v) => $q->whereDate('published_at', '<=', $v))),

                TernaryFilter::make('published_at')
                    ->label('Tanggal terbit')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Sudah bertanggal')
                    ->falseLabel('Tanpa tanggal'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('ajukan')
                        ->label('Ajukan untuk ditinjau')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->visible(fn (Post $record) => static::boleh($record, PostStatus::InReview))
                        ->action(fn (Post $record) => PostWorkflow::transition($record, PostStatus::InReview)),

                    Action::make('setujui')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-badge')
                        ->color('info')
                        ->schema([
                            Textarea::make('catatan')->label('Catatan tinjauan')->rows(2),
                        ])
                        ->visible(fn (Post $record) => static::boleh($record, PostStatus::Approved))
                        ->action(fn (Post $record, array $data) => PostWorkflow::transition(
                            $record, PostStatus::Approved, $data['catatan'] ?? null,
                        )),

                    Action::make('kembalikan')
                        ->label('Kembalikan ke draf')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        // Catatan wajib saat menolak. Naskah yang dikembalikan
                        // tanpa alasan hanya melahirkan percakapan di luar
                        // sistem, dan penulisnya menebak-nebak apa yang salah.
                        ->schema([
                            Textarea::make('catatan')->label('Alasan')->rows(2)->required(),
                        ])
                        ->visible(fn (Post $record) => static::boleh($record, PostStatus::Draft))
                        ->action(fn (Post $record, array $data) => PostWorkflow::transition(
                            $record, PostStatus::Draft, $data['catatan'],
                        )),

                    Action::make('terbitkan')
                        ->label('Terbitkan')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Post $record) => static::boleh($record, PostStatus::Published))
                        ->action(fn (Post $record) => PostWorkflow::transition($record, PostStatus::Published)),

                    Action::make('arsipkan')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Artikel ditarik dari situs. Isinya tetap tersimpan dan bisa diterbitkan lagi.')
                        ->visible(fn (Post $record) => static::boleh($record, PostStatus::Archived))
                        ->action(fn (Post $record) => PostWorkflow::transition($record, PostStatus::Archived)),

                    DeleteAction::make()
                        ->visible(fn () => auth()->user()?->canEditOthersContent() ?? false),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->canEditOthersContent() ?? false),
                ]),
            ]);
    }

    private static function boleh(Post $record, PostStatus $target): bool
    {
        return auth()->user() && PostWorkflow::allows(auth()->user(), $record, $target);
    }
}
