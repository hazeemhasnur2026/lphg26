<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        // Slug diturunkan dari nama dan tidak disunting terpisah. Tag dipakai
        // untuk menyaring, bukan sebagai halaman yang URL-nya dirancang orang.
        static::saving(function (Tag $tag) {
            $tag->slug = Str::slug($tag->name);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }
}
