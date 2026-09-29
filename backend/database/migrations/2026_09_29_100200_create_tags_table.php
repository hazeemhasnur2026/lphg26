<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tag (CMS-08).
 *
 * Tabel tersendiri, bukan kolom teks berisi daftar dipisah koma. Bentuk kedua
 * lebih cepat dibuat dan langsung menyulitkan hal yang justru jadi alasan tag
 * ada: menyaring. "energi" dan "Energi" menjadi dua hal, mengganti nama sebuah
 * tag berarti menyunting tiap artikel, dan menghitung ada berapa artikel per
 * tag tidak bisa dilakukan basis data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
    }
};
