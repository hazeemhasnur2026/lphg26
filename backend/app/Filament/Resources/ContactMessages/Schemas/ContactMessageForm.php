<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        /*
         * Seluruhnya hanya bisa dibaca. Ini catatan tentang apa yang benar-benar
         * dikirim seseorang. Menyuntingnya berarti mengubah rekaman itu, dan
         * balasan tetap dilakukan lewat surel, bukan dari sini.
         */
        return $schema->components([
            Section::make('Pengirim')->schema([
                TextInput::make('name')->label('Nama'),
                TextInput::make('email')->label('Surel'),
                TextInput::make('phone')->label('Telepon')->placeholder('tidak diisi'),
                TextInput::make('locale')->label('Bahasa'),
            ])->columns(2),

            Section::make('Pesan')->schema([
                TextInput::make('subject')->label('Perihal'),
                Textarea::make('message')->label('Isi pesan')->rows(10),
            ]),

            Section::make('Catatan sistem')->collapsed()->schema([
                TextInput::make('created_at')->label('Diterima'),
                TextInput::make('ip_address')->label('Alamat IP'),
            ])->columns(2),
        ])->disabled();
    }
}
