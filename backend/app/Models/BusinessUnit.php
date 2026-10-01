<?php

namespace App\Models;

use App\Support\Localization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class BusinessUnit extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name', 'slug', 'website_url', 'icon', 'image', 'image_alt',
        'tint_color', 'accent_color', 'short_description', 'full_content', 'order',
    ];

    /**
     * `full_content` tidak ikut di sini dengan sengaja: kolom itu tidak ada di
     * form panel sama sekali (halaman rinci SBU masih view Blade tersendiri,
     * lihat komentar getUrlAttribute() di bawah), jadi menerjemahkannya tidak
     * ada gunanya sampai kolom itu benar-benar dipakai.
     */
    public $translatable = ['name', 'short_description', 'image_alt'];

    /**
     * Halaman rinci tiap SBU masih berupa view Blade sendiri, bukan dari
     * `full_content`. Tata letaknya digarap satu per satu dan tidak bisa
     * diratakan jadi satu blok teks tanpa kehilangan bentuknya. URL-nya tetap
     * sama dengan situs lama.
     */
    public function getUrlAttribute(): string
    {
        return Localization::url("/page/our-business/{$this->slug}");
    }

    /**
     * Empat kartu pertama lebih sempit, sisanya lebih lebar, 4 lalu 3, sesuai
     * aset rujukan. Ini turunan urutan, jadi tidak perlu disimpan per baris.
     */
    public function columnSpan(int $index): int
    {
        return $index < 4 ? 3 : 4;
    }

    /** URL foto kartu; nilainya relatif terhadap disk `assets`. */
    public function getImageUrlAttribute(): ?string
    {
        return blank($this->image) ? null : Storage::disk('assets')->url($this->image);
    }

    public function getIconUrlAttribute(): ?string
    {
        return blank($this->icon) ? null : Storage::disk('assets')->url($this->icon);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
