<?php

namespace App\Models;

use App\Support\ImageVariants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body',
        'cover_image', 'published_at', 'seo_title', 'source_url',
        'external_url', 'source_name',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Where this item points.
     *
     * Press coverage links straight to the outlet that published it: we hold a
     * citation, not the article, so there is no page of our own to send people
     * to. Everything else keeps the old site's URL shape so links already
     * indexed survive the cutover.
     */
    public function getUrlAttribute(): string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        /*
         * URL-nya mengikuti bahasa yang sedang dibuka. Bukan soal kenyamanan:
         * halaman artikel memakai nilai ini sebagai canonical, dan kalau selalu
         * menunjuk versi Indonesia, halaman /en dan /ja menyatakan dirinya
         * duplikat. Google mengeluarkan keduanya dari indeks. Tautan di daftar
         * juga jadi melempar pembaca kembali ke bahasa Indonesia.
         */
        return LaravelLocalization::getLocalizedURL(
            app()->getLocale(),
            url("/post/{$this->category}/{$this->slug}"),
        );
    }

    public function isExternal(): bool
    {
        return filled($this->external_url);
    }

    /**
     * Where the cover image is served from.
     *
     * Nilainya relatif terhadap disk `content`, bentuk yang sama dengan yang
     * ditulis komponen unggahan di admin, jadi gambar hasil crawl dan gambar
     * yang diunggah orang tidak perlu diperlakukan berbeda.
     */
    public function getCoverUrlAttribute(): ?string
    {
        return blank($this->cover_image)
            ? null
            : Storage::disk('content')->url($this->cover_image);
    }

    /**
     * Body with image paths pointed at where Laravel serves them, and each
     * image given a WebP source.
     *
     * Rewriting on read rather than at import keeps the stored record matching
     * the crawl, so a re-import never has to undo a previous rewrite. The
     * originals are camera-resolution. Several are over 5 MB, so serving the
     * body untouched is what made article pages the heaviest on the site.
     */
    public function getBodyHtmlAttribute(): string
    {
        $html = str_replace('src="images/', 'src="/content-images/', $this->body ?? '');

        // Isi artikel dibatasi max-w-4xl (896 px), jadi varian 800/400 yang dipakai.
        return ImageVariants::enhance($html, '(min-width: 896px) 896px, 100vw');
    }

    /** Articles whose date still needs a human to confirm it. */
    public function scopeUndated($query)
    {
        return $query->whereNull('published_at');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
