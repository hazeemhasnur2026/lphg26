<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pustaka media terpusat (CMS-05).
 *
 * Sebelum ini tiap unggahan menempel pada satu field di satu formulir: sampul
 * artikel di sana, gambar SBU di sini. Berkasnya tersimpan rapi, tapi tidak ada
 * tempat untuk melihat apa saja yang pernah diunggah, tidak ada cara memakai
 * ulang satu foto di dua artikel tanpa mengunggahnya dua kali, dan alt text
 * hanya ada di tempat yang kebetulan sempat memikirkannya.
 *
 * `folder` disimpan sebagai teks biasa, bukan tabel folder dengan induk-anak.
 * Yang dibutuhkan redaksi adalah pengelompokan datar seperti "csr-2026", bukan
 * pohon berjenjang, dan pohon membawa pertanyaan yang tidak ada yang mau jawab:
 * apa yang terjadi pada isinya saat folder induk dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 32)->default('content');
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime_type', 100)->index();
            $table->unsignedBigInteger('size')->default(0);

            // Metadata yang dicari saat memakai ulang berkas.
            $table->string('title')->nullable();
            // Alt text nullable, bukan wajib: berkas PDF dan video tidak
            // memerlukannya, dan memaksa mengisinya di sana hanya melahirkan
            // alt text asal-asalan yang justru merusak pembaca layar.
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->string('folder')->nullable()->index();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('media_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['media_id', 'tag_id']);
        });

        // Galeri: satu artikel bertipe Galeri memuat banyak berkas, dan satu
        // berkas boleh muncul di lebih dari satu galeri.
        Schema::create('media_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->unique(['media_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_post');
        Schema::dropIfExists('media_tag');
        Schema::dropIfExists('media');
    }
};
