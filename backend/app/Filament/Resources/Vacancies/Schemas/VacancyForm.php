<?php

namespace App\Filament\Resources\Vacancies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Posisi')->schema([
                TextInput::make('title')
                    ->label('Nama posisi')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create'
                        ? $set('slug', Str::slug($state))
                        : null),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('department')->label('Unit / Departemen')->maxLength(255),
                TextInput::make('location')->label('Lokasi penempatan')->maxLength(255),
            ])->columns(2),

            Section::make('Rincian')->schema([
                RichEditor::make('description')->label('Deskripsi pekerjaan')->columnSpanFull(),
                RichEditor::make('requirements')->label('Persyaratan')->columnSpanFull(),
            ]),

            Section::make('Masa berlaku')->schema([
                DatePicker::make('deadline')
                    ->label('Tanggal tutup')
                    // Situs lama tidak pernah mencantumkan tenggat, jadi tanggal
                    // kosong harus berarti "masih dibuka", bukan "kedaluwarsa".
                    ->helperText('Kosongkan bila dibuka sampai ditutup manual. Setelah tanggal ini lewat, lowongan berhenti tampil.'),

                Toggle::make('is_open')
                    ->label('Sedang dibuka')
                    ->default(true)
                    ->helperText('Mematikannya menyembunyikan lowongan tanpa menghapus datanya.'),
            ])->columns(2),
        ]);
    }
}
