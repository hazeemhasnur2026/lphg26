<?php

use App\Enums\UserRole;
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
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default(UserRole::Editor->value)->after('email');
        });

        // Akun yang sudah ada dibuat sebelum peran ada, jadi tidak bisa
        // diasumsikan sebagai editor.
        DB::table('users')->update(['role' => UserRole::Administrator->value]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
