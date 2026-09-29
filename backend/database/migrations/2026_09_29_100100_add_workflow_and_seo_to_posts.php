<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom untuk alur redaksi (CMS-03), penjadwalan (CMS-07), SEO (CMS-06), dan
 * taksonomi (CMS-08).
 *
 * `status` dan `published_at` sengaja tidak digabung walaupun terlihat berlebih.
 * Keduanya menjawab hal berbeda dan bisa berselisih dengan sah: artikel yang
 * sudah disetujui dan dijadwalkan terbit lusa berstatus Approved dengan
 * published_at di masa depan. Satu kolom saja memaksa memilih salah satu makna
 * dan kehilangan yang lain.
 *
 * Pengisian nilai awal mengikuti keadaan yang sudah berlaku, bukan nilai bawaan
 * yang rapi: artikel yang selama ini tampil di situs harus tetap tampil setelah
 * migrasi ini jalan. Menyetel semuanya menjadi draf akan mengosongkan situs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Alur redaksi.
            $table->string('status', 20)->default('draft')->after('category')->index();
            $table->string('content_type', 30)->nullable()->after('status')->index();

            // Siapa yang memegang naskah. nullOnDelete, bukan cascade: akun yang
            // dihapus tidak boleh ikut membawa artikelnya.
            $table->foreignId('author_id')->nullable()->after('content_type')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('business_unit_id')->nullable()->after('author_id')
                ->constrained('business_units')->nullOnDelete();

            // Jejak tinjauan. Catatan peninjau disimpan supaya penulis tahu apa
            // yang harus diperbaiki tanpa harus bertanya di luar sistem.
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->foreignId('submitted_by')->nullable()->after('submitted_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('submitted_by');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                ->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable()->after('reviewed_by');
            $table->timestamp('archived_at')->nullable()->after('review_note');

            // Penjadwalan. Pasangan published_at: ada konten yang memang harus
            // berhenti tampil sendiri, misalnya pengumuman yang masa berlakunya
            // habis, dan menunggu orang ingat untuk menariknya tidak bisa
            // diandalkan.
            $table->timestamp('unpublish_at')->nullable()->after('archived_at')->index();

            // SEO. seo_title sudah ada sejak awal; sisanya menyusul.
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->string('og_image')->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('og_image');
            // Kontrol sitemap: halaman yang sengaja tidak ingin diindeks.
            $table->boolean('noindex')->default(false)->after('canonical_url');

            // Berkas untuk tipe Publikasi dan Laporan, tautan untuk tipe Video.
            $table->string('document_path')->nullable()->after('cover_image');
            $table->string('video_url')->nullable()->after('document_path');
        });

        // Keadaan yang sudah berlaku, dipindahkan ke kolom baru.
        //
        // Seluruh artikel menjadi 'published', termasuk yang tanggalnya kosong.
        // Ini terlihat salah sampai diperiksa apa yang sebenarnya berlaku
        // sekarang: PostController tidak menyaring apa pun, jadi 26 artikel
        // tanpa tanggal itu sudah tampil di situs hari ini. Menandainya draf
        // karena tanggalnya kosong akan menghapus 26 halaman dari situs pada
        // malam migrasi dijalankan, tanpa seorang pun memutuskan begitu.
        //
        // Tanggal yang kosong adalah pekerjaan yang belum selesai, dan tetap
        // muncul sebagai pekerjaan lewat penyaring "tanpa tanggal" di panel.
        // Bukan alasan untuk menarik artikelnya diam-diam.
        DB::table('posts')->update(['status' => 'published']);

        // Tipe ditebak dari kategori sekali saja. Sesudah ini orang yang memilih.
        foreach ([
            'press-release' => 'press_release',
            'csr-activity' => 'sustainability',
            'she-activity' => 'sustainability',
        ] as $kategori => $tipe) {
            DB::table('posts')->where('category', $kategori)->update(['content_type' => $tipe]);
        }

        DB::table('posts')->whereNull('content_type')->update(['content_type' => 'news']);

        // Ringkasan sudah dipakai sebagai meta description sejak awal lewat
        // view. Disalin ke kolomnya sendiri supaya sejak sekarang keduanya bisa
        // berbeda: ringkasan ditulis untuk pembaca kartu, meta description untuk
        // hasil pencarian, dan panjang idealnya tidak sama.
        DB::statement('UPDATE posts SET meta_description = excerpt WHERE meta_description IS NULL');
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->dropConstrainedForeignId('business_unit_id');
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropConstrainedForeignId('reviewed_by');

            $table->dropColumn([
                'status', 'content_type', 'submitted_at', 'reviewed_at',
                'review_note', 'archived_at', 'unpublish_at',
                'meta_description', 'og_image', 'canonical_url', 'noindex',
                'document_path', 'video_url',
            ]);
        });
    }
};
