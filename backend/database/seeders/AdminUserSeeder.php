<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun Super Admin pertama, dibaca dari .env — bukan ditulis di kode.
 *
 * Kredensial produksi tidak boleh ada di git selamanya, jadi seeder ini
 * sengaja tidak membawa email atau password sendiri. Isinya diambil dari
 * ADMIN_EMAIL/ADMIN_NAME/ADMIN_PASSWORD di .env server, berkas yang sudah
 * digitignore dan hanya pernah hidup di server itu.
 *
 * Dilewati begitu saja bila variabelnya kosong, dan tidak pernah menimpa akun
 * yang sudah ada. Ini pengisi akun pertama sekali saat basis data masih
 * kosong, bukan alat mereset password siapa pun tiap kali seeder dijalankan
 * ulang.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn(
                'ADMIN_EMAIL/ADMIN_PASSWORD kosong di .env, akun Super Admin pertama dilewati.'
            );

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Akun {$email} sudah ada, dilewati.");

            return;
        }

        User::create([
            'name' => env('ADMIN_NAME', 'Super Admin'),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'role' => UserRole::SuperAdmin,
        ]);

        $this->command?->info("Akun Super Admin {$email} dibuat.");
    }
}
