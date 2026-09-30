<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /**
     * Siapa yang boleh masuk panel admin.
     *
     * Tanpa ini Filament hanya mengizinkan akses saat APP_ENV=local, jadi di
     * produksi tidak ada satu pun akun yang bisa masuk, termasuk akun yang baru
     * dibuat lewat `php artisan make:filament-user`.
     *
     * Kedua peran boleh masuk. Yang membedakan keduanya adalah menu apa yang
     * terlihat setelah masuk, dan itu diputuskan di tiap resource.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Peran tidak bisa dinaikkan oleh orang yang tidak berhak mengurus akun.
     *
     * `role` tetap fillable karena formulir panel mengisinya lewat fill(), dan
     * mengeluarkannya berarti menambah forceFill di dua halaman. Yang dijaga di
     * sini bukan jalur itu melainkan semua jalur lain: satu $user->update($input)
     * di mana pun kelak akan membiarkan seseorang menulis role miliknya sendiri.
     *
     * Dijaga di model, bukan di form request, supaya perintah artisan dan rute
     * baru ikut terlindungi tanpa perlu ingat.
     *
     * Tanpa pengguna yang login tidak ada yang dihalangi: seeder, factory, dan
     * `make:filament-user` berjalan di luar HTTP dan memang berhak.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // Hanya penyuntingan akun yang sudah ada. Pembuatan akun sudah
            // tertutup di UserResource, yang hanya terbuka bagi Super Admin,
            // sedangkan factory dan seeder membuat akun berperan sepanjang
            // waktu dan tidak boleh ikut terhalang.
            if (! $user->exists || ! $user->isDirty('role')) {
                return;
            }

            $pelaku = auth()->user();

            if (! $pelaku) {
                return;
            }

            // Peran pelaku dibaca dari nilai yang tersimpan, bukan dari objek
            // di memori.
            //
            // Ini bukan kehati-hatian berlebih. Saat seseorang menaikkan
            // perannya sendiri, auth()->user() mengembalikan objek yang sama
            // dengan yang sedang disimpan — dan atributnya sudah berisi peran
            // baru yang ia berikan kepada dirinya sendiri. Bertanya kepada
            // objek itu sama dengan bertanya kepada penyerang apakah ia
            // berwenang, dan jawabannya selalu ya.
            $peranPelaku = $pelaku->is($user)
                ? UserRole::tryFrom((string) $user->getRawOriginal('role'))
                : $pelaku->role;

            if (! $peranPelaku?->canManageUsers()) {
                throw new \RuntimeException(
                    'Peran hanya dapat diubah oleh Super Admin.'
                );
            }
        });
    }

    /*
     * Autentikasi dua faktor lewat aplikasi (TOTP).
     *
     * Rahasianya dienkripsi lewat cast, jadi yang membaca tabel users langsung
     * tidak mendapatkan apa pun yang bisa dipakai. Nama pemegang yang muncul di
     * aplikasi autentikator memakai surel, bukan nama: satu orang bisa punya
     * akun di beberapa sistem, dan "Budi" di daftar tidak memberi tahu yang mana.
     */
    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Hak akses diteruskan ke enum, tidak diputuskan di sini.
     *
     * Kalau tiap model menjawab sendiri siapa boleh apa, aturannya tersebar dan
     * dua tempat akan menjawab berbeda begitu ada peran baru. Enum-nya satu
     * tempat yang bisa dibaca utuh dalam sekali lihat.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function canApproveContent(): bool
    {
        return $this->role->canApprove();
    }

    public function canPublishContent(): bool
    {
        return $this->role->canPublish();
    }

    public function canReadContactMessages(): bool
    {
        return $this->role->canReadContactMessages();
    }

    public function canManageUsers(): bool
    {
        return $this->role->canManageUsers();
    }

    public function canEditOthersContent(): bool
    {
        return $this->role->canEditOthersContent();
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            // Rahasia TOTP setara kata sandi kedua: yang memegangnya dapat
            // menghasilkan kode yang sah kapan saja. Tidak ditulis polos.
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }
}
