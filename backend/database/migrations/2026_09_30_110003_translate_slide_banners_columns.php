<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * title/subtitle/image_alt jadi kolom JSON per-bahasa. Lihat
 * 2026_09_30_110000_translate_posts_columns.php untuk penjelasan pola.
 */
return new class extends Migration
{
    private const KOLOM = ['title', 'subtitle', 'image_alt'];

    public function up(): void
    {
        Schema::table('slide_banners', function (Blueprint $table) {
            $table->json('title')->nullable()->change();
            $table->json('subtitle')->nullable()->change();
            $table->json('image_alt')->nullable()->change();
        });

        $this->bungkusData();
    }

    public function down(): void
    {
        $this->bukaBungkusData();

        Schema::table('slide_banners', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('subtitle')->nullable()->change();
            $table->string('image_alt')->nullable()->change();
        });
    }

    private function bungkusData(): void
    {
        DB::table('slide_banners')->orderBy('id')->chunkById(200, function ($baris) {
            foreach ($baris as $row) {
                $update = [];

                foreach (self::KOLOM as $col) {
                    $nilai = $row->$col;

                    if (is_null($nilai)) {
                        continue;
                    }

                    if (str_starts_with(trim($nilai), '{') && json_decode($nilai, true) !== null) {
                        continue;
                    }

                    $update[$col] = json_encode(['id' => $nilai], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                if ($update) {
                    DB::table('slide_banners')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    private function bukaBungkusData(): void
    {
        DB::table('slide_banners')->orderBy('id')->chunkById(200, function ($baris) {
            foreach ($baris as $row) {
                $update = [];

                foreach (self::KOLOM as $col) {
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
                    DB::table('slide_banners')->where('id', $row->id)->update($update);
                }
            }
        });
    }
};
