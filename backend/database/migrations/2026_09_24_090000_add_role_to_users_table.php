<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Peran pengguna.
 *
 * Akun yang sudah ada dijadikan administrator, bukan editor. Menurunkan hak akun
 * yang sedang dipakai berarti mengunci orang di luar panelnya sendiri, dan itu
 * cara yang buruk untuk memperkenalkan sistem peran.
 *
 * Nilainya ditulis sebagai teks biasa, bukan diambil dari App\Enums\UserRole.
 * Migrasi adalah catatan tentang bentuk basis data pada suatu titik waktu, dan
 * enum ikut berubah seiring aplikasi: saat 'administrator' dan 'editor' diganti
 * empat peran baru, migrasi ini akan memanggil case yang sudah tidak ada dan
 * seluruh `migrate` pada basis data kosong berhenti dengan fatal error. Migrasi
 * berikutnyalah yang memetakan nilai lama ini ke peran baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('editor')->after('email');
        });

        // Akun yang sudah ada dibuat sebelum peran ada, jadi tidak bisa
        // diasumsikan sebagai editor.
        DB::table('users')->update(['role' => 'administrator']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
