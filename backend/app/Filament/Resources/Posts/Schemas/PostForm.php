<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Isi')->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    // Slug hanya diisi otomatis saat membuat baru. Mengisinya
                    // ulang saat menyunting akan mengubah URL artikel yang sudah
                    // terbit hanya karena judulnya dirapikan.
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create'
                        ? $set('slug', Str::slug($state))
                        : null)
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Bagian akhir URL artikel. Mengubahnya memutus tautan yang sudah terindeks Google. URL lama akan menjawab 404.')
                    ->columnSpanFull(),

                Select::make('category')
                    ->label('Kategori')
                    ->options(fn () => collect(config('site.listings'))->map(fn ($l) => $l['label'])->all())
                    ->required()
                    // Terisi lebih dulu bila form dibuka dari menu seksi
                    // tertentu, tapi tetap bisa diubah. Memindahkan artikel
                    // antar kategori adalah hal yang wajar.
                    ->default(fn () => config('site.listings.'.request()->query('category', '')) ? request()->query('category') : null)
                    ->helperText('Menentukan halaman daftar tempat artikel ini muncul.'),

                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Tampil di kartu daftar dan dipakai sebagai meta description bila ada.')
                    ->columnSpanFull(),

                RichEditor::make('body')
                    ->label('Isi artikel')
                    ->helperText('Kosongkan bila ini hanya catatan liputan media. Lihat bagian paling bawah.')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Penerbitan')->schema([
                DateTimePicker::make('published_at')
                    ->label('Tanggal terbit')
                    ->helperText('Artikel tanpa tanggal tidak ikut diimpor ulang dan URL-nya dijawab 410.'),

                TextInput::make('seo_title')
                    ->label('Judul SEO')
                    ->maxLength(255)
                    ->helperText('Kosongkan untuk memakai judul di atas.'),

                FileUpload::make('cover_image')
                    ->label('Gambar sampul')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->disk('content')
                    ->directory('unggahan')
                    ->visibility('public')
                    ->helperText('Setelah mengunggah gambar baru, jalankan tools/optimize_images.py --tree content agar versi WebP-nya ikut dibuat.')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Liputan media')
                ->description('Diisi hanya bila artikelnya terbit di media lain. Isi beritanya milik penerbit dan tidak disalin ke sini, yang dicatat cuma judul, media, tanggal, dan tautannya.')
                ->collapsed()
                ->schema([
                    TextInput::make('external_url')
                        ->label('Tautan ke media')
                        ->url()
                        ->maxLength(255)
                        ->helperText('Bila diisi, kartu di daftar akan membuka tautan ini, bukan halaman kita.'),

                    TextInput::make('source_name')
                        ->label('Nama media')
                        ->maxLength(255),

                    TextInput::make('source_url')
                        ->label('URL asal di situs lama')
                        ->url()
                        ->maxLength(255)
                        ->helperText('Diisi otomatis oleh importer. Dipakai untuk menelusuri asal artikel hasil migrasi.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
