<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
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
class User extends Authenticatable implements FilamentUser
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
        ];
    }
}
