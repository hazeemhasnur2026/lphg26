<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengumuman yang muncul sekali saat pengunjung pertama kali membuka situs.
 *
 * Tabel tersendiri, bukan satu baris pengaturan. Pengumuman punya masa berlaku,
 * dan yang sudah lewat masanya layak tetap tersimpan: tahun depan akan ada yang
 * bertanya bunyi pengumuman Lebaran tahun ini, dan satu baris yang ditimpa
 * terus-menerus tidak bisa menjawabnya.
 *
 * starts_at dan ends_at mengikuti pola yang sama dengan published_at dan
 * unpublish_at pada artikel: pengumuman dijadwalkan, bukan dinyalakan dan
 * dimatikan tangan oleh orang yang harus ingat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();

            // Tombol ajakan, keduanya boleh kosong. Pengumuman yang hanya
            // memberi tahu tidak perlu mengirim orang ke mana-mana.
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();

            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index();

            // Dua pengumuman aktif bersamaan adalah keadaan yang sah — misalnya
            // yang satu berlaku sampai akhir bulan dan yang baru mulai pekan
            // depan. Urutan menentukan mana yang tampil bila keduanya berlaku
            // pada hari yang sama.
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
