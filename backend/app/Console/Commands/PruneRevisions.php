<?php

namespace App\Console\Commands;

use App\Models\PostRevision;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Memangkas riwayat versi yang melewati batas simpan.
 *
 * Tidak memakai model:prune bawaan Laravel. Trait Prunable memilih baris lewat
 * satu kondisi where, sedangkan aturan di sini — "sisakan lima puluh terakhir
 * milik tiap artikel" — bergantung pada peringkat sebuah baris di dalam
 * kelompoknya. Memaksakannya ke dalam where menghasilkan subkueri per baris,
 * dan pada tabel yang justru dipangkas karena besar, itu bentuk paling lambat
 * yang mungkin.
 */
class PruneRevisions extends Command
{
    protected $signature = 'revisions:prune {--dry : hitung saja, jangan hapus}';

    protected $description = 'Hapus revisi artikel yang melewati batas simpan';

    public function handle(): int
    {
        $batas = (int) config('site.retensi_revisi');

        if ($batas <= 0) {
            $this->info('Pemangkasan revisi dimatikan (site.retensi_revisi = 0).');

            return self::SUCCESS;
        }

        // Artikel yang revisinya melebihi batas. Disaring lebih dulu supaya
        // yang diperiksa satu per satu hanya artikel yang memang bermasalah,
        // bukan seluruh isi tabel.
        $kandidat = PostRevision::query()
            ->select('post_id', DB::raw('count(*) as jumlah'))
            ->groupBy('post_id')
            ->having('jumlah', '>', $batas)
            ->pluck('jumlah', 'post_id');

        if ($kandidat->isEmpty()) {
            $this->info("Tidak ada artikel yang melewati {$batas} versi.");

            return self::SUCCESS;
        }

        $total = 0;

        foreach ($kandidat as $postId => $jumlah) {
            // Versi tertinggi yang boleh tetap tinggal. Yang di bawahnya dibuang
            // dalam satu perintah, bukan satu per satu.
            $ambang = PostRevision::where('post_id', $postId)
                ->orderByDesc('version')
                ->skip($batas - 1)
                ->value('version');

            $akanHilang = PostRevision::where('post_id', $postId)
                ->where('version', '<', $ambang);

            $total += $this->option('dry')
                ? $akanHilang->count()
                : $akanHilang->delete();
        }

        $kata = $this->option('dry') ? 'akan dihapus' : 'dihapus';
        $this->info(sprintf('%d revisi %s dari %d artikel. Batas: %d versi per artikel.',
            $total, $kata, $kandidat->count(), $batas));

        return self::SUCCESS;
    }
}
