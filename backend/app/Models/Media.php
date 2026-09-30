<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /**
     * Laravel menebak nama tabel dari nama model dengan memakai bentuk jamak
     * bahasa Inggris, dan bentuk jamak "media" menurut aturan itu adalah
     * "medias". Tabelnya bernama media.
     */
    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'original_name', 'mime_type', 'size',
        'title', 'alt_text', 'caption', 'folder', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withPivot('position');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isDocument(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /** Ukuran berkas dalam bentuk yang bisa dibaca orang. */
    public function getSizeForHumansAttribute(): string
    {
        $bytes = $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $satuan) {
            if ($bytes < 1024) {
                return round($bytes, $satuan === 'B' ? 0 : 1).' '.$satuan;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
