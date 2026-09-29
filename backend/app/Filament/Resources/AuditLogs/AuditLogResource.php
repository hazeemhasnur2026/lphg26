<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Jejak audit: hanya bisa dibaca.
 *
 * Tidak ada tombol sunting dan tidak ada tombol hapus, juga untuk Super Admin.
 * Jejak audit yang bisa dirapikan oleh orang yang paling berkepentingan
 * merapikannya tidak menjawab apa pun saat dibutuhkan.
 */
class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Jejak Audit';

    protected static ?string $pluralModelLabel = 'Jejak Audit';

    public static function canAccess(): bool
    {
        return auth()->user()?->canApproveContent() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Oleh')
                    ->placeholder('sistem')
                    ->searchable()
                    // Nama disalin saat peristiwa terjadi; relasi dipakai hanya
                    // untuk menandai akun yang sudah tidak ada lagi.
                    ->description(fn (AuditLog $record) => $record->user_id && ! $record->user
                        ? 'akun sudah dihapus'
                        : null),

                TextColumn::make('action')
                    ->label('Tindakan')
                    ->badge()
                    ->color(fn (string $state) => match (true) {
                        str_contains($state, 'deleted') => 'danger',
                        str_contains($state, 'status') => 'warning',
                        str_contains($state, 'reverted') => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Tindakan')
                    ->options(fn () => AuditLog::query()
                        ->distinct()->orderBy('action')->pluck('action', 'action')->all())
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('rentang')
                    ->schema([
                        DatePicker::make('dari')->label('Dari'),
                        DatePicker::make('sampai')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['sampai'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAuditLogs::route('/')];
    }
}
