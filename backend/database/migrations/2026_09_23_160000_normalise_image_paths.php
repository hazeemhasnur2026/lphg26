<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Samakan bentuk path gambar jadi relatif terhadap disk-nya.
 *
 * Tiga bentuk berbeda tersimpan sebelumnya, masing-masing warisan dari cara
 * datanya masuk: crawler menulis "images/kategori/slug/1.jpg", sementara konten
 * yang disalin dari Blade menulis URL absolut "/assets/img/...". Keduanya
 * bekerja karena view menambal selisihnya saat render.
 *
 * Itu berhenti bekerja begitu ada form admin: komponen unggahan menyimpan path
 * relatif terhadap disk, jadi data lama tidak akan bisa dipratinjau dan gambar
 * baru mendarat di ruang nama yang lain. Menyamakannya sekarang lebih murah
 * daripada menyimpan dua bentuk selamanya.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')->whereNotNull('cover_image')->update([
            'cover_image' => DB::raw("REPLACE(cover_image, 'images/', '')"),
        ]);

        foreach (['business_units' => ['image', 'icon'], 'slide_banners' => ['image']] as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)->whereNotNull($column)->update([
                    $column => DB::raw("REPLACE({$column}, '/assets/img/', '')"),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('posts')->whereNotNull('cover_image')->update([
            'cover_image' => DB::raw("'images/' || cover_image"),
        ]);

        foreach (['business_units' => ['image', 'icon'], 'slide_banners' => ['image']] as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)->whereNotNull($column)->update([
                    $column => DB::raw("'/assets/img/' || {$column}"),
                ]);
            }
        }
    }
};
