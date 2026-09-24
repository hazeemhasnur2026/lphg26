<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            // Bahasa yang dipakai pengirim saat mengisi form, supaya balasannya
            // bisa memakai bahasa yang sama.
            $table->string('locale', 5)->default('id');
            $table->ipAddress('ip_address')->nullable();
            // Ditandai sudah ditangani dari admin; pesan tidak dihapus supaya
            // riwayatnya tetap ada.
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
