<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'department', 'location',
        'description', 'requirements', 'deadline', 'is_open',
    ];

    protected function casts(): array
    {
        return ['deadline' => 'date', 'is_open' => 'boolean'];
    }

    /**
     * Lowongan yang masih dibuka. Tanggal tutup kosong berarti dibuka sampai
     * ditutup manual. Situs lama tidak pernah mencantumkan tenggat, jadi
     * mensyaratkan tanggal akan menyembunyikan lowongan yang sah.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_open', true)
            ->where(fn (Builder $q) => $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()))
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->orderBy('id');
    }
}
