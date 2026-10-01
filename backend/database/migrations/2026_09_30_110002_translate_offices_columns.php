<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * name/address jadi kolom JSON per-bahasa. Lihat
 * 2026_09_30_110000_translate_posts_columns.php untuk penjelasan pola.
 *
 * address tetap tidak-nullable (tidak pernah ->nullable() di migrasi asal),
 * dan Office::addressLines() tidak perlu berubah: accessor tetap mengembalikan
 * string locale aktif seperti sebelumnya.
 */
return new class extends Migration
{
    private const KOLOM = ['name', 'address'];

    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('address')->change();
        });

        $this->bungkusData();
    }

    public function down(): void
    {
        $this->bukaBungkusData();

        Schema::table('offices', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('address')->change();
        });
    }

    private function bungkusData(): void
    {
        DB::table('offices')->orderBy('id')->chunkById(200, function ($baris) {
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
                    DB::table('offices')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    private function bukaBungkusData(): void
    {
        DB::table('offices')->orderBy('id')->chunkById(200, function ($baris) {
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
                    DB::table('offices')->where('id', $row->id)->update($update);
                }
            }
        });
    }
};
