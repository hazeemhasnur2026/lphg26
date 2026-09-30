<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dua peran menjadi empat, sesuai CMS-02 pada TOR.
 *
 * Pemetaannya menaikkan hak, tidak menurunkan. 'administrator' menjadi Super
 * Admin karena memang sudah memegang segalanya; 'editor' menjadi Admin Korporat
 * karena itu yang paling dekat dengan haknya sekarang, yaitu seluruh konten
 * tanpa pengelolaan akun.
 *
 * Godaan yang ditolak di sini: memetakan 'editor' menjadi Kontributor supaya
 * alur persetujuan langsung terasa. Itu berarti semua orang yang kemarin bisa
 * menerbitkan, pagi ini tidak bisa, tanpa pernah diberi tahu. Peran yang lebih
 * sempit diberikan kepada orang, bukan diwariskan diam-diam oleh migrasi.
 */
return new class extends Migration
{
    private const PEMETAAN = [
        'administrator' => 'super_admin',
        'editor' => 'admin',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'super_admin' tidak muat di string(20) milik migrasi lama? Muat,
            // tapi hanya dengan dua karakter sisa. Dilonggarkan sekarang selagi
            // tabelnya kecil, bukan nanti saat ada peran bernama panjang.
            $table->string('role', 40)->default('contributor')->change();
        });

        foreach (self::PEMETAAN as $lama => $baru) {
            DB::table('users')->where('role', $lama)->update(['role' => $baru]);
        }
    }

    public function down(): void
    {
        foreach (self::PEMETAAN as $lama => $baru) {
            DB::table('users')->where('role', $baru)->update(['role' => $lama]);
        }

        // Peran yang tidak punya padanan lama disamakan dengan editor: itu yang
        // paling sedikit haknya di antara dua peran lama.
        DB::table('users')->whereNotIn('role', ['administrator', 'editor'])
            ->update(['role' => 'editor']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('editor')->change();
        });
    }
};
