<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Enums\PostStatus;
use App\Models\Concerns\MengoptimalkanGambar;
use App\Support\ImageVariants;
use App\Support\SafeHtml;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasFactory, HasTranslations, MengoptimalkanGambar;

    public $translatable = ['title', 'excerpt', 'body', 'seo_title', 'meta_description'];

    /** Kolom gambar pada disk `content`. */
    public function kolomGambar(): array
    {
        return ['cover_image', 'og_image'];
    }

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body',
        'cover_image', 'published_at', 'seo_title', 'source_url',
        'external_url', 'source_name',
        'status', 'content_type', 'author_id', 'business_unit_id',
        'submitted_at', 'submitted_by', 'reviewed_at', 'reviewed_by',
        'review_note', 'archived_at', 'unpublish_at',
        'meta_description', 'og_image', 'canonical_url', 'noindex',
        'document_path', 'video_url',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'unpublish_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'archived_at' => 'datetime',
            'noindex' => 'boolean',
            'status' => PostStatus::class,
            'content_type' => ContentType::class,
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** Berkas galeri, untuk artikel bertipe Galeri. */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)->withPivot('position')->orderBy('position');
    }

    /**
     * Tanpa urutan bawaan, dengan sengaja.
     *
     * ->latest('version') di sini terasa membantu dan justru menipu: urutan
     * yang melekat pada relasi ikut ke setiap query turunannya, dan orderBy
     * yang ditulis pemanggil ditambahkan *sesudahnya*, bukan menggantikannya.
     * Hasilnya ORDER BY version DESC, version ASC — pemanggil yang meminta
     * urutan naik tetap menerima urutan turun, tanpa galat apa pun.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class);
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
        // Disaring lebih dulu, sebelum apa pun yang lain.
        //
        // View mencetak nilai ini dengan {!! !!} karena badan artikel memang
        // HTML. Tanpa langkah ini, siapa pun yang boleh mengisi badan artikel
        // dapat menyisipkan <script> yang berjalan di browser tiap pengunjung.
        //
        // Disaring saat dibaca, bukan saat disimpan, dengan alasan yang sama
        // seperti penulisan ulang path di bawah: yang tersimpan tetap sama
        // dengan hasil crawl. Ada untungnya juga — bila kelak ditemukan celah
        // pada daftar izinnya, memperbaiki daftar itu langsung berlaku untuk
        // seluruh artikel lama tanpa perlu memproses ulang satu pun baris.
        $html = SafeHtml::clean($this->body);

        $html = str_replace('src="images/', 'src="/content-images/', $html);

        // Sesudah disaring, bukan sebelum: <picture> dan <source> yang
        // ditambahkan di sini markup kami sendiri, dan penyaringnya tidak
        // mengenal keduanya.
        //
        // Isi artikel dibatasi max-w-4xl (896 px), jadi varian 800/400 yang dipakai.
        return ImageVariants::enhance($html, '(min-width: 896px) 896px, 100vw');
    }

    /** Articles whose date still needs a human to confirm it. */
    public function scopeUndated($query)
    {
        return $query->whereNull('published_at');
    }

    /**
     * Apa yang boleh dilihat pengunjung.
     *
     * Tiga syarat, dan yang di tengah tampak longgar dengan sengaja. Tanggal
     * yang kosong tidak menyembunyikan artikel, karena 26 artikel hasil migrasi
     * memang belum punya tanggal dan sudah tampil sejak situs ini menyala.
     * Yang menyembunyikan artikel adalah tanggal yang ditulis untuk masa depan
     * — itu penjadwalan, dan itu memang diminta.
     */
    public function scopePublished($query)
    {
        return $query
            ->where('status', PostStatus::Published)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('unpublish_at')->orWhere('unpublish_at', '>', now()));
    }

    /** Naskah yang sedang menunggu seseorang membacanya. */
    public function scopeAwaitingReview($query)
    {
        return $query->where('status', PostStatus::InReview);
    }

    public function isVisibleToPublic(): bool
    {
        return $this->status === PostStatus::Published
            && ($this->published_at === null || $this->published_at->isPast())
            && ($this->unpublish_at === null || $this->unpublish_at->isFuture());
    }

    /**
     * Meta description untuk hasil pencarian.
     *
     * Ringkasan dipakai sebagai cadangan, bukan sebagai sumber utama. Keduanya
     * ditulis untuk pembaca yang berbeda: ringkasan untuk orang yang sudah ada
     * di halaman daftar, meta description untuk orang yang belum memutuskan
     * mengklik.
     */
    public function getMetaDescriptionTextAttribute(): string
    {
        return $this->meta_description ?: ($this->excerpt ?: $this->title);
    }

    /** Gambar untuk kartu berbagi. Jatuh ke sampul bila tidak disetel sendiri. */
    public function getOgImageUrlAttribute(): ?string
    {
        $path = $this->og_image ?: $this->cover_image;

        return blank($path) ? null : Storage::disk('content')->url($path);
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return blank($this->document_path)
            ? null
            : Storage::disk('content')->url($this->document_path);
    }

    /**
     * Ukuran berkas unduhan, dalam bentuk yang bisa dibaca orang.
     *
     * Dibaca dari disk saat ditampilkan, bukan disimpan di kolom sendiri: satu
     * pembacaan metadata per halaman tidak terasa, sedangkan kolom yang
     * disimpan akan berselisih dengan berkasnya begitu berkas itu diganti dari
     * luar panel.
     *
     * Null bila berkasnya tidak ada. Itu bukan keadaan mustahil — berkas bisa
     * terhapus dari disk sementara barisnya tetap ada — dan tombol unduh tanpa
     * keterangan ukuran masih jauh lebih baik daripada halaman yang gagal
     * dirender seluruhnya.
     */
    public function getDocumentSizeAttribute(): ?string
    {
        if (blank($this->document_path)) {
            return null;
        }

        try {
            $disk = Storage::disk('content');

            if (! $disk->exists($this->document_path)) {
                return null;
            }

            $bytes = $disk->size($this->document_path);
        } catch (\Throwable) {
            return null;
        }

        foreach (['B', 'KB', 'MB', 'GB'] as $satuan) {
            if ($bytes < 1024) {
                return round($bytes, $satuan === 'B' ? 0 : 1).' '.$satuan;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }

    /** Tautan video dalam bentuk yang bisa ditanam, bila penyedianya dikenali. */
    public function getVideoEmbedUrlAttribute(): ?string
    {
        return VideoEmbed::url($this->video_url);
    }

    /**
     * Menyimpan salinan keadaan sekarang sebagai satu versi.
     *
     * Dipanggil sebelum perubahan ditulis, jadi yang tersimpan adalah isi yang
     * akan ditinggalkan — itu yang dicari orang saat ingin kembali.
     */
    public function recordRevision(?string $summary = null): PostRevision
    {
        /*
         * ->only() memanggil accessor, dan accessor field translatable hanya
         * mengembalikan locale yang aktif saat ini (selalu 'id' di panel admin,
         * yang berada di luar grup rute berbahasa). Snapshot yang dibangun lewat
         * ->only() akan diam-diam membuang separuh terjemahan Inggris setiap
         * kali versi baru dicatat. getTranslations() mengambil kedua bahasa.
         */
        $snapshot = collect(self::REVISIONED_FIELDS)->mapWithKeys(fn (string $field) => [
            $field => in_array($field, $this->translatable, true)
                ? $this->getTranslations($field)
                : $this->getAttribute($field),
        ])->all();

        return $this->revisions()->create([
            'version' => ($this->revisions()->max('version') ?? 0) + 1,
            'snapshot' => $snapshot,
            'summary' => $summary,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Kolom yang ikut disalin ke sebuah revisi.
     *
     * Hanya isi naskah. Status, jejak tinjauan, dan tanggal terbit tidak masuk:
     * mengembalikan versi lama adalah soal mengembalikan tulisan, bukan
     * membatalkan persetujuan yang sudah diberikan orang lain.
     */
    public const REVISIONED_FIELDS = [
        'title', 'slug', 'category', 'content_type', 'excerpt', 'body',
        'cover_image', 'document_path', 'video_url',
        'seo_title', 'meta_description', 'og_image', 'canonical_url', 'noindex',
        'external_url', 'source_name',
    ];
}
