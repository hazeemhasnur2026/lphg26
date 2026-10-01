<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * title/body/image_alt/cta_label jadi kolom JSON per-bahasa. Lihat
 * 2026_09_30_110000_translate_posts_columns.php untuk penjelasan pola.
 */
return new class extends Migration
{
    private const KOLOM = ['title', 'body', 'image_alt', 'cta_label'];

    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('body')->nullable()->change();
            $table->json('image_alt')->nullable()->change();
            $table->json('cta_label')->nullable()->change();
        });

        $this->bungkusData();
    }

    public function down(): void
    {
        $this->bukaBungkusData();

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('body')->nullable()->change();
            $table->string('image_alt')->nullable()->change();
            $table->string('cta_label')->nullable()->change();
        });
    }

    private function bungkusData(): void
    {
        DB::table('announcements')->orderBy('id')->chunkById(200, function ($baris) {
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
                    DB::table('announcements')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    private function bukaBungkusData(): void
    {
        DB::table('announcements')->orderBy('id')->chunkById(200, function ($baris) {
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
                    DB::table('announcements')->where('id', $row->id)->update($update);
                }
            }
        });
    }
};
