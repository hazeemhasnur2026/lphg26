<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Satu peristiwa analitik yang dicatat situs sendiri.
 *
 * Tidak punya updated_at. Peristiwa adalah sesuatu yang terjadi pada satu
 * saat; ia tidak pernah disunting sesudahnya, dan kolom yang isinya selalu
 * sama dengan created_at hanya menambah pekerjaan bagi yang membacanya.
 */
class AnalyticsEvent extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /**
     * Peristiwa yang diterima, beserta nama yang dipakai di panel.
     *
     * Empat yang pertama adalah yang diminta TOR. Yang kelima ikut karena
     * sudah lama terkirim ke dataLayer oleh skrip yang sama, dan membuangnya
     * berarti membuang data yang kebetulan sudah ada.
     *
     * Daftar ini sekaligus menjadi penyaring di ujung penerima: nama di luar
     * daftar ditolak. Tanpa itu, siapa pun yang menemukan alamat endpoint-nya
     * bisa menanam nama peristiwa karangan sendiri ke dalam papan analitik,
     * dan tidak ada cara membedakannya dari yang asli sesudahnya.
     */
    public const PERISTIWA = [
        'cta_click' => 'Klik CTA',
        'file_download' => 'Unduhan berkas',
        'search' => 'Pencarian',
        'content_read' => 'Artikel dibaca tuntas',
        'outbound_click' => 'Tautan ke luar situs',
    ];

    protected $fillable = ['name', 'label', 'path', 'locale', 'visitor', 'demo', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'demo' => 'boolean'];
    }

    public function namaPeristiwa(): string
    {
        return self::PERISTIWA[$this->name] ?? $this->name;
    }

    /**
     * Mencatat satu peristiwa.
     *
     * Panjang label dipotong di sini, bukan ditolak lewat aturan validasi.
     * Yang mengirim adalah skrip di halaman, bukan orang yang mengisi formulir:
     * judul artikel yang kebetulan 200 karakter adalah judul yang sah, dan
     * membalas 422 kepadanya berarti kehilangan peristiwanya sama sekali demi
     * empat puluh karakter yang tidak pernah dibaca siapa pun.
     */
    public static function catat(string $nama, ?string $label, ?string $jalur, ?string $bahasa, Request $request): ?self
    {
        if (! array_key_exists($nama, self::PERISTIWA)) {
            return null;
        }

        return static::create([
            'name' => $nama,
            'label' => $label ? Str::limit(trim($label), 157) : null,
            'path' => $jalur ? Str::limit($jalur, 252) : null,
            'locale' => $bahasa ? substr($bahasa, 0, 5) : null,
            'visitor' => static::sidik($request),
            'created_at' => now(),
        ]);
    }

    /**
     * Sidik pengunjung: cukup untuk membedakan orang, tidak cukup untuk
     * menunjuk orang.
     *
     * Hasil HMAC dari alamat IP dan User-Agent bersama tanggal hari ini, jadi
     * nilainya berganti tiap tengah malam dan baris kemarin tidak bisa
     * dihubungkan ke baris hari ini. Kuncinya APP_KEY, sehingga sidik yang
     * sama tidak bisa dihitung ulang dari luar untuk mencocokkan seseorang
     * yang alamat IP-nya sudah diketahui.
     */
    protected static function sidik(Request $request): string
    {
        return substr(hash_hmac(
            'sha256',
            implode('|', [$request->ip(), $request->userAgent(), now()->toDateString()]),
            (string) config('app.key'),
        ), 0, 32);
    }

    /**
     * Baris yang melewati masa simpan.
     *
     * Papan analitik tidak pernah menanyakan apa pun tentang bulan ke-14, dan
     * tabel peristiwa adalah tabel yang tumbuh paling cepat di basis data ini:
     * tiap klik satu baris. Dibiarkan tanpa batas, ia menjadi bagian terbesar
     * dari tiap backup harian tanpa ada yang pernah membacanya.
     *
     * Disetel 0 berarti pemangkasan dimatikan. Kuerinya lalu dibuat tidak
     * pernah cocok dengan baris mana pun, bukan dibiarkan tanpa syarat —
     * salah ketik pada satu setelan tidak boleh berujung pada terhapusnya
     * seluruh riwayat.
     */
    public function prunable(): Builder
    {
        $bulan = (int) config('site.retensi_analitik_bulan');

        if ($bulan <= 0) {
            return static::query()->whereRaw('1 = 0');
        }

        return static::query()->where('created_at', '<', now()->subMonths($bulan));
    }

    public function scopeSejak(Builder $query, int $hari): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($hari)->startOfDay());
    }

    /** Baris contoh yang dibuat perintah analitik:contoh. */
    public function scopeContoh(Builder $query): Builder
    {
        return $query->where('demo', true);
    }
}
