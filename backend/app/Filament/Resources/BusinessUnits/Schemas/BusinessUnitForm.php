<?php

namespace App\Filament\Resources\BusinessUnits\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kartu di beranda')->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    // Halaman rincinya masih view Blade, bukan dari database,
                    // jadi slug yang tidak punya view akan menghasilkan 404.
                    ->helperText('Harus cocok dengan halaman yang ada di /page/our-business/{slug}.'),

                TextInput::make('website_url')
                    ->label('Situs resmi unit')
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://')
                    // Tombolnya tidak tampil selama kolom ini kosong, jadi
                    // mengosongkannya adalah cara menyembunyikan tombol itu.
                    ->helperText('Bila diisi, halaman unit ini menampilkan tombol menuju situs tersebut. Kosongkan bila belum ada.')
                    ->columnSpanFull(),

                Textarea::make('short_description')
                    ->label('Deskripsi singkat')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Menentukan posisi kartu. Empat kartu pertama tampil lebih sempit, sesuai aset rujukan.'),
            ])->columns(2),

            Section::make('Gambar')->schema([
                FileUpload::make('image')
                    ->label('Foto kartu')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->disk('assets')
                    ->directory('pages')
                    ->visibility('public')
                    ->helperText('Setelah mengunggah, jalankan tools/optimize_images.py --tree assets agar versi WebP-nya ikut dibuat.'),

                TextInput::make('image_alt')
                    ->label('Teks alternatif foto')
                    ->maxLength(255)
                    ->helperText('Dibaca pembaca layar dan mesin pencari. Jelaskan isi fotonya, bukan ulangi nama unit.'),

                FileUpload::make('icon')
                    ->label('Ikon bulat')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->disk('assets')
                    ->directory('bisnis')
                    ->visibility('public'),
            ])->columns(2),

            Section::make('Warna')
                ->description('Diambil dari panduan merek. Latar dipakai untuk bidang kartu sekaligus sapuan di atas fotonya; aksen untuk tombol panah.')
                ->schema([
                    ColorPicker::make('tint_color')->label('Warna latar')->required(),
                    ColorPicker::make('accent_color')->label('Warna aksen')->required(),
                ])->columns(2),
        ]);
    }
}
