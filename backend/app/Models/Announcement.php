<?php

namespace App\Models;

use App\Models\Concerns\MengoptimalkanGambar;
use App\Support\SafeHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Announcement extends Model
{
    use HasFactory, HasTranslations, MengoptimalkanGambar;

    public function kolomGambar(): array
    {
        return ['image'];
    }

    protected $fillable = [
        'title', 'body', 'show_text', 'image', 'image_alt',
        'cta_label', 'cta_url', 'is_active',
        'starts_at', 'ends_at', 'order',
    ];

    public $translatable = ['title', 'body', 'image_alt', 'cta_label'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_text' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Pengumuman yang berlaku sekarang.
     *
     * Tanggal yang kosong berarti tanpa batas di sisi itu: starts_at kosong
     * berarti berlaku sejak diaktifkan, ends_at kosong berarti sampai
     * dimatikan. Keduanya kosong adalah keadaan yang paling sering dipakai, dan
     * tidak boleh memaksa orang mengisi tanggal yang tidak ia pedulikan.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->orderByDesc('order')
            ->orderByDesc('id');
    }

    /** Yang ditampilkan hari ini, atau null bila tidak ada. */
    public static function sekarang(): ?self
    {
        return static::active()->first();
    }

    public function getImageUrlAttribute(): ?string
    {
        return blank($this->image) ? null : Storage::disk('content')->url($this->image);
    }

    /**
     * Isi pengumuman, disaring.
     *
     * Ditulis orang lewat editor kaya di panel, lalu dicetak ke halaman dengan
     * {!! !!} — persis bentuk yang menjadi celah XSS pada badan artikel. Dilewat
     * kan penyaring yang sama sejak awal, bukan menunggu ada yang menemukannya.
     */
    public function getBodyHtmlAttribute(): string
    {
        return SafeHtml::clean($this->body);
    }

    /**
     * Penanda versi yang disimpan peramban pengunjung.
     *
     * Ikut berubah tiap pengumuman disunting, sehingga perbaikan salah ketik
     * pun memunculkannya kembali pada orang yang sudah menutupnya. Itu memang
     * yang diinginkan: yang sudah dibaca orang bukan lagi isi yang sekarang.
     */
    public function getSignatureAttribute(): string
    {
        return $this->id.'-'.($this->updated_at?->timestamp ?? 0);
    }
}
