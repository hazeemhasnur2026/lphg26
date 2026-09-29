<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat revisi (CMS-09) dan jejak audit (CMS-03).
 *
 * Dua tabel, bukan satu, karena keduanya menjawab pertanyaan yang berbeda dan
 * dibaca orang yang berbeda.
 *
 * Revisi menjawab "apa isi naskah ini sebelum diubah", dan gunanya untuk
 * dikembalikan. Isinya salinan penuh, jadi berat, dan hanya berlaku untuk
 * artikel.
 *
 * Jejak audit menjawab "siapa melakukan apa, kapan", dan gunanya untuk
 * ditelusuri saat ada yang mempertanyakan. Isinya ringan, tidak pernah dihapus,
 * dan berlaku untuk model apa pun, termasuk penghapusan — tepat pada saat
 * revisinya ikut hilang.
 *
 * Menggabungkan keduanya berarti setiap pencatatan tindakan ikut menyalin badan
 * artikel, atau setiap pengembalian versi harus menebak isi dari catatan yang
 * tidak menyimpannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            // Nomor urut per artikel, bukan id global: yang ditanya orang adalah
            // "versi ke berapa", dan id global menjawabnya dengan angka yang
            // melompat-lompat karena dipakai bersama artikel lain.
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->string('summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'version']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Polimorfik dan nullable: sebagian peristiwa tidak menempel pada
            // baris mana pun, misalnya seseorang masuk panel.
            $table->nullableMorphs('auditable');
            $table->string('action', 60)->index();
            $table->string('description')->nullable();
            $table->json('changes')->nullable();

            // Nama pelaku ikut disalin, bukan hanya id-nya. Akun bisa dihapus,
            // dan jejak audit yang menunjuk "pengguna #7" tanpa ada pengguna #7
            // tidak menjawab apa pun saat justru sedang dibutuhkan.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('post_revisions');
    }
};
