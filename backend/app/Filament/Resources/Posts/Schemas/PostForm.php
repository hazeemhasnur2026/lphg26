<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\ContentType;
use App\Enums\PostStatus;
use App\Models\BusinessUnit;
use App\Models\Post;
use App\Support\PostWorkflow;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    /**
     * Nilai content_type dari formulir, apa pun bentuknya.
     *
     * Filament mengembalikan enum saat nilainya berasal dari record yang sudah
     * ada — model men-cast kolomnya — tetapi string saat berasal dari pilihan
     * yang baru diklik. Memaksa (string) pada enum melempar fatal error, dan
     * membandingkan langsung dengan ->value gagal diam-diam pada kasus yang
     * satunya. Keduanya dinormalkan di sini, sekali.
     */
    private static function tipe(mixed $nilai): ?ContentType
    {
        return $nilai instanceof ContentType
            ? $nilai
            : ContentType::tryFrom((string) $nilai);
    }

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
                    ->helperText('Menentukan halaman daftar tempat artikel ini muncul, dan bentuk URL-nya.'),

                Select::make('content_type')
                    ->label('Tipe konten')
                    ->options(ContentType::class)
                    ->required()
                    ->default(ContentType::News)
                    ->live()
                    ->helperText('Bentuk kontennya. Berbeda dari kategori: satu daftar boleh memuat beberapa tipe.'),

                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Tampil di kartu daftar.')
                    ->columnSpanFull(),

                RichEditor::make('body')
                    ->label('Isi artikel')
                    ->helperText('Kosongkan bila ini hanya catatan liputan media atau berkas unduhan.')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Berkas')
                ->description('Terisi sesuai tipe konten yang dipilih di atas.')
                ->schema([
                    FileUpload::make('cover_image')
                        ->label('Gambar sampul')
                        // Wajib pada artikel yang sudah terbit, bebas pada draf.
                        //
                        // Mewajibkannya sejak draf berarti penulis tidak bisa
                        // menyimpan tulisan setengah jadi tanpa lebih dulu
                        // mencari gambar — dan yang terjadi berikutnya adalah
                        // gambar asal-asalan yang dipasang supaya tombol simpan
                        // menyala. Syaratnya berlaku di gerbang terbit.
                        ->required(fn (?Post $record) => $record?->status === PostStatus::Published)
                        ->helperText(fn (?Post $record) => $record?->status === PostStatus::Published
                            ? 'Wajib: artikel yang sudah terbit harus punya gambar sampul.'
                            : 'Wajib diisi sebelum artikel dapat diterbitkan. Setelah mengunggah gambar baru, jalankan tools/optimize_images.py --tree content agar versi WebP-nya ikut dibuat.')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(4096)
                        ->disk('content')
                        ->directory('unggahan')
                        ->visibility('public')
                        ->columnSpanFull(),

                    FileUpload::make('document_path')
                        ->label('Berkas PDF')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(16384)
                        ->disk('content')
                        ->directory('unggahan/dokumen')
                        ->visibility('public')
                        ->visible(fn ($get) => static::tipe($get('content_type'))?->isDocument() ?? false)
                        ->helperText('Isi laporan atau publikasi yang diunduh pembaca.')
                        ->columnSpanFull(),

                    TextInput::make('video_url')
                        ->label('Tautan video')
                        ->url()
                        ->maxLength(255)
                        ->visible(fn ($get) => static::tipe($get('content_type')) === ContentType::Video)
                        ->helperText('Tautan YouTube atau sumber video lain.')
                        ->columnSpanFull(),

                    Select::make('media')
                        ->label('Berkas galeri')
                        ->relationship('media', 'original_name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->visible(fn ($get) => static::tipe($get('content_type')) === ContentType::Gallery)
                        // Dipilih dari pustaka, bukan diunggah di sini. Foto
                        // galeri adalah foto yang paling sering dipakai ulang,
                        // dan mengunggahnya per artikel melahirkan salinan
                        // ketiga dari berkas yang sama — masing-masing dengan
                        // alt text sendiri yang harus ditulis ulang.
                        ->helperText('Diambil dari Pustaka Media. Unggah berkasnya di sana lebih dulu.')
                        ->columnSpanFull(),
                ]),

            Section::make('Penerbitan')->schema([
                Placeholder::make('status_sekarang')
                    ->label('Status')
                    ->content(fn ($record) => $record?->status
                        ? $record->status->getLabel().' — '.$record->status->description()
                        : 'Draf — belum disimpan.')
                    ->columnSpanFull(),

                DateTimePicker::make('published_at')
                    ->label('Tanggal terbit')
                    ->seconds(false)
                    ->helperText('Boleh diisi masa depan: artikel baru muncul setelah waktu itu lewat. Waktu WIB.'),

                DateTimePicker::make('unpublish_at')
                    ->label('Berhenti tampil')
                    ->seconds(false)
                    ->after('published_at')
                    ->helperText('Kosongkan bila artikel tidak punya masa berlaku. Waktu WIB.'),

                Select::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id()),

                Select::make('business_unit_id')
                    ->label('Unit bisnis')
                    ->options(fn () => BusinessUnit::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Tidak terkait unit tertentu'),

                Select::make('tags')
                    ->label('Tag')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    // Tag baru boleh dibuat dari sini. Memaksa orang membuka
                    // menu lain lebih dulu adalah cara paling pasti membuat tag
                    // tidak pernah dipakai.
                    ->createOptionForm([
                        TextInput::make('name')->label('Nama')->required()->unique('tags', 'name'),
                    ])
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('SEO')
                ->description('Kosongkan yang tidak perlu; masing-masing punya cadangan yang masuk akal.')
                ->collapsed()
                ->schema([
                    TextInput::make('seo_title')
                        ->label('Judul SEO')
                        ->maxLength(255)
                        ->helperText('Kosong berarti memakai judul artikel.'),

                    TextInput::make('canonical_url')
                        ->label('URL kanonik')
                        ->url()
                        ->maxLength(255)
                        ->helperText('Kosong berarti memakai URL artikel ini sendiri.'),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(2)
                        ->maxLength(320)
                        ->helperText('Tampil di hasil pencarian. Kosong berarti memakai ringkasan.')
                        ->columnSpanFull(),

                    FileUpload::make('og_image')
                        ->label('Gambar berbagi (OG image)')
                        ->image()
                        ->disk('content')
                        ->directory('unggahan/og')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->helperText('Tampil saat tautan dibagikan. Kosong berarti memakai gambar sampul.')
                        ->columnSpanFull(),

                    Toggle::make('noindex')
                        ->label('Jangan indeks halaman ini')
                        ->helperText('Mengeluarkannya dari sitemap dan meminta mesin pencari melewatinya.')
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
        ])
            // Naskah yang sedang ditinjau tidak boleh berubah di bawah tangan
            // peninjaunya, dan kontributor tidak memegang naskah orang lain.
            ->disabled(fn ($record) => $record && auth()->user()
                && ! PostWorkflow::canEdit(auth()->user(), $record));
    }
}
