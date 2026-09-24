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

    public function isAdministrator(): bool
    {
        return $this->role === UserRole::Administrator;
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
