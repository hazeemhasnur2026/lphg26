<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom yang ternyata dibutuhkan begitu konten kartu SBU dan slider beranda
 * dipindah dari Blade ke database.
 *
 * Warna tidak dipindahkan ke sini karena "biar rapi": tiap kartu SBU punya
 * warna latar dan warna tombol sendiri yang diambil dari panduan merek, dan
 * kalau warnanya tetap tertinggal di Blade maka menambah SBU dari admin
 * menghasilkan kartu tanpa warna. Lebar kolom dan jeda animasi sebaliknya
 * memang tidak disimpan. Keduanya murni turunan dari urutan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_units', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('image');
            $table->string('tint_color', 7)->nullable()->after('image_alt');
            $table->string('accent_color', 7)->nullable()->after('tint_color');
        });

        Schema::table('slide_banners', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('business_units', function (Blueprint $table) {
            $table->dropColumn(['image_alt', 'tint_color', 'accent_color']);
        });

        Schema::table('slide_banners', function (Blueprint $table) {
            $table->dropColumn('image_alt');
        });
    }
};
