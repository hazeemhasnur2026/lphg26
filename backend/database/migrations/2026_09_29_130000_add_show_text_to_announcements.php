<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mode poster: gambar saja, tanpa judul dan isi di atasnya.
 *
 * Sebagian pengumuman datang sebagai selebaran yang teksnya sudah tercetak di
 * dalam gambar. Menampilkan judul dan isi di bawahnya berarti mengulang hal
 * yang sama dua kali, dan yang kedua selalu kalah rapi.
 *
 * Judulnya tetap wajib diisi, dan sengaja begitu: ia nama pengumuman ini di
 * daftar panel, dan ia juga nama yang dibacakan pembaca layar saat dialognya
 * terbuka. Yang dimatikan tombol ini hanya penampilannya di layar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('show_text')->default(true)->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('show_text');
        });
    }
};
