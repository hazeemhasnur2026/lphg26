<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageForm;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Publikasi';

    protected static ?int $navigationSort = 9;

    protected static ?string $modelLabel = 'Pesan Masuk';

    protected static ?string $pluralModelLabel = 'Pesan Masuk';

    /** Jumlah pesan yang belum ditangani, tampil sebagai lencana di menu. */
    public static function getNavigationBadge(): ?string
    {
        $unhandled = static::getModel()::unhandled()->count();

        return $unhandled > 0 ? (string) $unhandled : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Pesan hanya masuk lewat formulir publik, tidak pernah dibuat dari sini. */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Hanya administrator.
     *
     * Isi pesan dari pengunjung adalah data pribadi: nama, surel, nomor telepon,
     * dan alamat IP. Orang yang tugasnya menyunting artikel tidak perlu
     * melihatnya, dan membatasi siapa yang bisa adalah bentuk paling sederhana
     * dari pembatasan akses yang diminta UU PDP.
     *
     * canAccess menutup menunya sekaligus URL-nya; menyembunyikan menu saja
     * tidak menghalangi siapa pun yang menebak alamatnya.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->canReadContactMessages() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return ContactMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
