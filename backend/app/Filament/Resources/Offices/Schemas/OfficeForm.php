<?php

namespace App\Filament\Resources\Offices\Schemas;

use App\Models\Office;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama kantor')
                ->required()
                ->maxLength(255),

            Select::make('region')
                ->label('Jenis')
                ->options([
                    Office::HEADQUARTERS => 'Kantor Pusat',
                    Office::REPRESENTATIVE => 'Kantor Perwakilan',
                ])
                ->required()
                // Footer menampilkan kantor pusat di kolom sendiri, lengkap
                // dengan telepon dan surel; perwakilan hanya alamat.
                ->helperText('Menentukan kolom mana di footer yang menampilkannya.'),

            Textarea::make('address')
                ->label('Alamat')
                ->required()
                ->rows(3)
                ->helperText('Satu baris di sini menjadi satu baris di footer. Tidak perlu menulis tag HTML.')
                ->columnSpanFull(),

            TextInput::make('phone')->label('Telepon')->tel()->maxLength(255),
            TextInput::make('fax')->label('Faks')->maxLength(255),
            TextInput::make('email')->label('Surel')->email()->maxLength(255),
            TextInput::make('order')->label('Urutan')->numeric()->default(0),
        ])->columns(2);
    }
}
