<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Catatan satu tindakan yang mengubah sesuatu.
 *
 * Hanya punya created_at. Baris di sini tidak pernah disunting — begitu jejak
 * audit bisa diubah, ia berhenti menjadi jejak audit — jadi updated_at akan
 * selalu sama dengan created_at dan hanya menambah kolom yang membingungkan
 * pembacanya.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'auditable_type', 'auditable_id', 'action', 'description',
        'changes', 'user_id', 'user_name', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mencatat sebuah tindakan.
     *
     * Dipanggil dari tempat tindakannya terjadi, bukan dari event model.
     * Observer akan menangkap semua penyimpanan dan menghasilkan catatan yang
     * benar tapi tidak berarti: "Post #12 diubah" tidak memberi tahu apakah
     * yang terjadi adalah salah ketik yang diperbaiki atau artikel yang ditarik
     * dari situs. Yang layak dicatat adalah peristiwa redaksional, dan hanya
     * pemanggilnya yang tahu peristiwa apa itu.
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $changes = null,
    ): self {
        $user = auth()->user();

        return static::create([
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
            'user_id' => $user?->getKey(),
            'user_name' => $user?->name,
            'ip_address' => request()->ip(),
        ]);
    }
}
