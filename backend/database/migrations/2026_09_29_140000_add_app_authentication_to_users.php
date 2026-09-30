<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autentikasi dua faktor lewat aplikasi (TOTP).
 *
 * Kedua kolom menyimpan rahasia, dan keduanya dienkripsi di lapisan model, bukan
 * ditulis polos. Yang memegang secret TOTP dapat menghasilkan kode yang sah
 * kapan saja; ia setara kata sandi kedua, bukan sekadar nomor.
 *
 * Kode pemulihan disimpan juga. Tanpa itu, kehilangan ponsel berarti kehilangan
 * akun, dan jalan keluar satu-satunya menjadi mematikan MFA lewat basis data —
 * yang dalam prakteknya berarti MFA dimatikan untuk semua orang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('app_authentication_secret')->nullable()->after('password');
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};
