<?php

namespace App\Filament\Resources\Announcements;

use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\EditAnnouncement;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Situs';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'Pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Isi')->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->translatable(),

                RichEditor::make('body')
                    ->label('Isi')
                    ->helperText('Boleh dikosongkan bila judulnya sudah cukup.')
                    ->visible(fn ($get) => (bool) $get('show_text'))
                    ->columnSpanFull()
                    ->translatable(),

                Toggle::make('show_text')
                    ->label('Tampilkan judul dan isi')
                    ->default(true)
                    ->live()
                    ->helperText('Matikan bila pengumumannya berupa selebaran yang teksnya sudah tercetak di gambar. Judul tetap dipakai sebagai nama di daftar ini dan dibacakan pembaca layar.')
                    ->columnSpanFull(),

                FileUpload::make('image')
                    ->label('Gambar')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->disk('content')
                    ->directory('unggahan/pengumuman')
                    ->visibility('public')
                    ->columnSpanFull(),

                TextInput::make('image_alt')
                    ->label('Alt text gambar')
                    ->maxLength(255)
                    ->required(fn ($get) => ! $get('show_text') && filled($get('image')))
                    // Wajib pada mode poster, dan alasannya bukan formalitas:
                    // di mode itu gambar adalah satu-satunya isi, sehingga
                    // pengguna pembaca layar tidak menerima apa pun selain alt
                    // text ini. Tuliskan pesan pokoknya, bukan "gambar poster".
                    ->helperText(fn ($get) => $get('show_text')
                        ? 'Apa yang terlihat di gambar, untuk pembaca layar.'
                        : 'Wajib: gambar adalah satu-satunya isi, jadi tulis pesan pokoknya di sini.')
                    ->columnSpanFull()
                    ->translatable(),
            ]),

            Section::make('Tombol')
                ->description('Isi keduanya, atau kosongkan keduanya. Tombol tanpa tujuan tidak ditampilkan.')
                ->schema([
                    TextInput::make('cta_label')
                        ->label('Teks tombol')
                        ->maxLength(60)
                        ->placeholder('Selengkapnya')
                        ->translatable(),

                    TextInput::make('cta_url')
                        ->label('Tujuan tombol')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://…'),
                ])->columns(2),

            Section::make('Tayang')->schema([
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Mematikan ini menyembunyikannya seketika, apa pun tanggalnya.')
                    ->columnSpanFull(),

                DateTimePicker::make('starts_at')
                    ->label('Mulai tampil')
                    ->seconds(false)
                    ->helperText('Kosongkan untuk mulai sejak diaktifkan. Waktu WIB.'),

                DateTimePicker::make('ends_at')
                    ->label('Berhenti tampil')
                    ->seconds(false)
                    ->after('starts_at')
                    ->helperText('Kosongkan bila tanpa batas waktu. Waktu WIB.'),

                TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Bila ada lebih dari satu yang berlaku bersamaan, angka terbesar yang tampil.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('Gambar')->height(40),

                TextColumn::make('title')
                    ->label('Judul')
                    // title kolom JSON translatable: ->searchable() bawaan
                    // membuat where "title" like ..., yang tidak akan pernah
                    // cocok terhadap isi JSON. Query eksplisit menyisir kedua
                    // bahasa.
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('title->id', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%"))
                    ->limit(50)
                    ->wrap(),

                IconColumn::make('is_active')->label('Aktif')->boolean(),

                IconColumn::make('show_text')
                    ->label('Ada teks')
                    ->boolean()
                    ->toggleable(),

                // Menjawab pertanyaan yang sebenarnya: apakah ini sedang
                // terlihat pengunjung? "Aktif" saja tidak cukup — tanggalnya
                // bisa belum tiba atau sudah lewat.
                IconColumn::make('tampil')
                    ->label('Sedang tampil')
                    ->boolean()
                    ->state(fn (Announcement $record) => Announcement::active()
                        ->whereKey($record->getKey())->exists()),

                TextColumn::make('starts_at')->label('Mulai')->dateTime('d M Y H:i')->placeholder('—'),
                TextColumn::make('ends_at')->label('Selesai')->dateTime('d M Y H:i')->placeholder('—'),
                TextColumn::make('order')->label('Urutan')->sortable()->toggleable(),
            ])
            ->defaultSort('order', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
