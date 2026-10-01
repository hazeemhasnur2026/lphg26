<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * title/excerpt/body/seo_title/meta_description jadi kolom JSON berisi peta
 * per-bahasa ({"id": "...", "en": "..."}), dipakai spatie/laravel-translatable
 * (App\Models\Post::$translatable).
 *
 * Data lama tidak boleh hilang: setiap nilai string yang sudah ada dibungkus
 * jadi {"id": nilai_lama} pada baris yang sama, bukan ditulis ulang dari nol.
 * JSON_UNESCAPED_SLASHES wajib di sini — body artikel berisi HTML dengan URL
 * bergaris miring (<img src="...">, <a href="...">), dan tanpa flag itu
 * json_encode mengubah setiap "/" jadi "\/" di dalam markup yang tersimpan.
 */
return new class extends Migration
{
    private const KOLOM_WAJIB = ['title', 'body'];

    private const KOLOM_OPSIONAL = ['excerpt', 'seo_title', 'meta_description'];

    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('excerpt')->nullable()->change();
            $table->json('body')->change();
            $table->json('seo_title')->nullable()->change();
            $table->json('meta_description')->nullable()->change();
        });

        $this->bungkusData();
    }

    public function down(): void
    {
        $this->bukaBungkusData();

        Schema::table('posts', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('excerpt')->nullable()->change();
            $table->longText('body')->change();
            $table->string('seo_title')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
        });
    }

    private function bungkusData(): void
    {
        $kolom = [...self::KOLOM_WAJIB, ...self::KOLOM_OPSIONAL];

        DB::table('posts')->orderBy('id')->chunkById(200, function ($baris) use ($kolom) {
            foreach ($baris as $row) {
                $update = [];

                foreach ($kolom as $col) {
                    $nilai = $row->$col;

                    if (is_null($nilai)) {
                        continue;
                    }

                    // Jaga idempotensi: migrasi ini aman dijalankan ulang bila
                    // sempat terhenti di tengah jalan.
                    if (str_starts_with(trim($nilai), '{') && json_decode($nilai, true) !== null) {
                        continue;
                    }

                    $update[$col] = json_encode(['id' => $nilai], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                if ($update) {
                    DB::table('posts')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    private function bukaBungkusData(): void
    {
        $kolom = [...self::KOLOM_WAJIB, ...self::KOLOM_OPSIONAL];

        DB::table('posts')->orderBy('id')->chunkById(200, function ($baris) use ($kolom) {
            foreach ($baris as $row) {
                $update = [];

                foreach ($kolom as $col) {
                    $nilai = $row->$col;

                    if (is_null($nilai)) {
                        continue;
                    }

                    $terurai = json_decode($nilai, true);

                    if (! is_array($terurai)) {
                        continue;
                    }

                    $update[$col] = $terurai['id'] ?? (reset($terurai) ?: '');
                }

                if ($update) {
                    DB::table('posts')->where('id', $row->id)->update($update);
                }
            }
        });
    }
};
