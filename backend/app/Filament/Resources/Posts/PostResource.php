<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\RelationManagers\RevisionsRelationManager;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->map(fn (array $listing, string $category) => NavigationItem::make(__($listing['label']))
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
            RevisionsRelationManager::class,
        ];
    }

    /**
     * Kontributor hanya melihat naskahnya sendiri.
     *
     * Disaring di query, bukan disembunyikan di tampilan. Menyembunyikan baris
     * dari tabel tidak menutup URL /admin/posts/{id}/edit, dan nomor yang
     * berurutan adalah undangan untuk mencoba.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        return $user && ! $user->canEditOthersContent()
            ? $query->where('author_id', $user->getKey())
            : $query;
    }

    /**
     * Angka merah di menu: naskah yang menunggu ditinjau.
     *
     * Hanya ditampilkan kepada yang bisa berbuat sesuatu terhadapnya. Bagi
     * kontributor angka itu hanya pengingat bahwa ia sedang menunggu orang lain.
     */
    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->canApproveContent()) {
            return null;
        }

        $menunggu = static::getModel()::awaitingReview()->count();

        return $menunggu > 0 ? (string) $menunggu : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
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
