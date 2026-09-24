<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Publikasi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Artikel';

    protected static ?string $pluralModelLabel = 'Artikel';

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    /**
     * Satu menu per kategori, bukan satu pintu "Artikel" saja.
     *
     * Kategori CSR dan SHE sejak awal ada di dropdown form, tapi tidak terlihat
     * dari menu: orang harus tahu lebih dulu untuk membuka Artikel lalu memilih
     * kategorinya. Menu ini mengikuti bentuk navigasi situs publik, jadi tempat
     * menambah artikel CSR ada di bawah CSR.
     */
    public static function getNavigationItems(): array
    {
        return collect(config('site.listings'))
            ->map(fn (array $listing, string $category) => NavigationItem::make($listing['label'])
                ->group(static::navigationGroupFor($listing['section']))
                ->icon(static::$navigationIcon)
                ->sort(static::$navigationSort)
                ->url(static::getUrl('index', ['category' => $category]))
                ->isActiveWhen(fn () => request()->query('category') === $category))
            ->values()
            ->all();
    }

    /**
     * Nama grup di sidebar. Label panjang di config/site.php ("Tanggung Jawab
     * Sosial Perusahaan") dipakai untuk breadcrumb halaman publik dan terlalu
     * panjang untuk menu samping.
     */
    public static function navigationGroupFor(string $section): string
    {
        return match ($section) {
            'csr' => 'CSR',
            'she' => 'SHE',
            default => 'Publikasi',
        };
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
