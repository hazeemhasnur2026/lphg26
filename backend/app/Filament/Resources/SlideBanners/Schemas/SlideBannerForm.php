<?php

namespace App\Filament\Resources\SlideBanners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SlideBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')
                ->label('Gambar')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(4096)
                ->disk('assets')
                ->directory('gallery')
                ->visibility('public')
                ->required()
                // Bingkainya 28:9 dan selebar layar, jadi gambar kecil akan
                // teregang. Foto yang ada sekarang hanya 550 px dan memang
                // terlihat kurang tajam.
                ->helperText('Tampil selebar layar dengan rasio 28:9. Pakai gambar minimal 1600 px agar tidak pecah. Setelah mengunggah, jalankan tools/optimize_images.py --tree assets.')
                ->columnSpanFull(),

            TextInput::make('image_alt')
                ->label('Teks alternatif')
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('title')->label('Judul')->maxLength(255),
            TextInput::make('subtitle')->label('Keterangan')->maxLength(255),

            TextInput::make('order')->label('Urutan')->numeric()->default(0),

            Toggle::make('is_active')
                ->label('Tampilkan')
                ->default(true)
                ->helperText('Slide yang dimatikan tetap tersimpan tapi tidak ikut tampil.'),
        ])->columns(2);
    }
}
