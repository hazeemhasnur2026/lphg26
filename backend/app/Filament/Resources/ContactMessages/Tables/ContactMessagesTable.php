<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('handled_at')
                    ->label('Ditangani')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('name')->label('Pengirim')->searchable(),
                TextColumn::make('email')->label('Surel')->searchable()->copyable(),
                TextColumn::make('subject')->label('Perihal')->searchable()->limit(50)->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('handled_at')
                    ->label('Status')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Sudah ditangani')
                    ->falseLabel('Belum ditangani'),
            ])
            ->recordActions([
                ViewAction::make(),

                // Menandai, bukan menghapus: riwayat siapa yang pernah menghubungi
                // tetap ada walau pesannya sudah dijawab.
                Action::make('toggleHandled')
                    ->label(fn (ContactMessage $record) => $record->isHandled() ? 'Tandai belum' : 'Tandai sudah')
                    ->icon('heroicon-o-check-circle')
                    ->color(fn (ContactMessage $record) => $record->isHandled() ? 'gray' : 'success')
                    ->action(fn (ContactMessage $record) => $record->update([
                        'handled_at' => $record->isHandled() ? null : now(),
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
