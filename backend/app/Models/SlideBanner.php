<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class SlideBanner extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'image', 'image_alt', 'title', 'subtitle', 'order', 'is_active',
    ];

    public $translatable = ['title', 'subtitle', 'image_alt'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** URL gambar slide; nilainya relatif terhadap disk `assets`. */
    public function getImageUrlAttribute(): ?string
    {
        return blank($this->image) ? null : Storage::disk('assets')->url($this->image);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('order')->orderBy('id');
    }
}
