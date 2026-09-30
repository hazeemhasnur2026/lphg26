<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Sepuluh peristiwa terakhir di panel.
 *
 * Isinya sama dengan menu Jejak Audit, dipotong sepuluh baris. Alasannya bukan
 * kemudahan: jejak audit yang hanya dibuka saat ada masalah tidak pernah
 * sempat menjadi kebiasaan, dan kekeliruan paling murah diperbaiki pada hari
 * yang sama ia terjadi.
 */
class AktivitasTerakhirWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return 'Aktivitas Terakhir';
    }

    public static function canView(): bool
    {
        return auth()->user()?->canApproveContent() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->latest('created_at')->limit(10))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->tooltip(fn (AuditLog $record) => $record->created_at?->format('d M Y H:i')),

                TextColumn::make('user_name')
                    ->label('Oleh')
                    ->placeholder('sistem'),

                TextColumn::make('description')
                    ->label('Peristiwa')
                    ->wrap()
                    ->placeholder(fn (AuditLog $record) => $record->action),
            ])
            ->paginated(false);
    }
}
