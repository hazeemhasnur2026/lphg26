<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * Pesan dari formulir Hubungi Kami.
 *
 * Disimpan, bukan sekadar dikirim sebagai surel. Pengiriman surel bisa gagal
 * karena hal di luar kendali situs. Kredensial SMTP kedaluwarsa, kuota habis,
 * penyedia menolak, dan kalau pesannya hanya lewat surel, kegagalan seperti itu
 * berarti pertanyaan calon mitra hilang tanpa jejak.
 */
class ContactMessage extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'locale', 'ip_address', 'handled_at',
    ];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    /**
     * Pesan yang sudah melewati masa simpan.
     *
     * Bila masa simpan disetel 0, kueri ini sengaja dibuat tidak pernah cocok
     * dengan baris mana pun. Mengembalikan kueri kosong lebih aman daripada
     * mengembalikan kueri tanpa syarat: salah ketik pada satu setelan tidak
     * boleh berujung pada terhapusnya seluruh pesan.
     */
    public function prunable(): Builder
    {
        $bulan = (int) config('site.retensi_pesan_bulan');

        if ($bulan <= 0) {
            return static::query()->whereRaw('1 = 0');
        }

        return static::query()->where('created_at', '<', now()->subMonths($bulan));
    }

    public function scopeUnhandled(Builder $query): Builder
    {
        return $query->whereNull('handled_at');
    }

    public function isHandled(): bool
    {
        return filled($this->handled_at);
    }
}
