<?php

namespace App\Filament\Resources\MediaLibrary\Schemas;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Berkas')->schema([
                FileUpload::make('path')
                    ->label('Berkas')
                    ->required()
                    ->disk('content')
                    ->directory('pustaka')
                    ->visibility('public')
                    ->maxSize(16384)
                    ->acceptedFileTypes([
                        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                        'video/mp4', 'video/webm',
                        'application/pdf',
                    ])
                    // Nama asli, tipe, dan ukuran diambil dari berkasnya sendiri
                    // saat diunggah, bukan diketik orang. Metadata yang diketik
                    // tangan akan berselisih dengan berkasnya cepat atau lambat.
                    ->storeFileNamesIn('original_name')
                    ->columnSpanFull(),
            ]),

            Section::make('Keterangan')
                ->description('Yang membuat berkas ini bisa ditemukan lagi enam bulan lagi.')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->maxLength(255),

                    TextInput::make('folder')
                        ->label('Folder')
                        ->maxLength(255)
                        ->datalist(fn () => Media::whereNotNull('folder')
                            ->distinct()->pluck('folder')->all())
                        ->helperText('Pengelompokan bebas, misalnya "csr-2026". Boleh dikosongkan.'),

                    TextInput::make('alt_text')
                        ->label('Alt text')
                        ->maxLength(255)
                        ->helperText('Apa yang terlihat di gambar, untuk pembaca layar dan saat gambar gagal dimuat. Kosongkan untuk PDF dan video.')
                        ->columnSpanFull(),

                    Textarea::make('caption')
                        ->label('Kredit / keterangan')
                        ->rows(2)
                        ->columnSpanFull(),

                    Select::make('tags')
                        ->label('Tag')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')->label('Nama')->required()->unique('tags', 'name'),
                        ])
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
