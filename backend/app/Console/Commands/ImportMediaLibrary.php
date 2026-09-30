<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Mendaftarkan berkas yang sudah ada di disk ke pustaka media.
 *
 * Gambar hasil crawl situs lama sudah dilayani ke pembaca sejak hari pertama —
 * artikel menunjuknya langsung lewat path di badan tulisan. Yang tidak ada
 * adalah barisnya di tabel media, dan tanpa itu tidak ada cara menemukan,
 * memberi alt text, atau memakai ulang satu foto tanpa mengunggahnya lagi.
 *
 * Perintah ini tidak memindahkan dan tidak mengubah satu berkas pun. Ia hanya
 * mencatat apa yang sudah ada. Berkas yang barisnya sudah ada dilewati, jadi
 * menjalankannya dua kali tidak menggandakan apa pun.
 *
 * Alt text sengaja dibiarkan kosong. Menebaknya dari nama berkas menghasilkan
 * "dsc-0042" sebagai keterangan gambar bagi pengguna pembaca layar, yang lebih
 * buruk daripada tidak ada sama sekali: yang kosong muncul di penyaring
 * "Belum ada alt text" dan di dasbor, yang terisi asal-asalan tidak.
 */
class ImportMediaLibrary extends Command
{
    protected $signature = 'media:import
                            {--folder= : hanya berkas di dalam subfolder ini}
                            {--dry : tampilkan apa yang akan didaftarkan, tanpa menulis}';

    protected $description = 'Daftarkan berkas yang sudah ada di disk content ke pustaka media';

    /** Jenis yang layak masuk pustaka, dipetakan ke tipe MIME-nya. */
    private const JENIS = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'gif' => 'image/gif',
        'mp4' => 'video/mp4', 'webm' => 'video/webm',
        'pdf' => 'application/pdf',
    ];

    public function handle(): int
    {
        $disk = Storage::disk('content');
        $dry = (bool) $this->option('dry');
        $folder = trim((string) $this->option('folder'), '/');

        $berkas = collect($disk->allFiles($folder ?: null))
            ->reject(fn (string $path) => Str::startsWith(basename($path), '.'))
            ->filter(fn (string $path) => isset(self::JENIS[strtolower(pathinfo($path, PATHINFO_EXTENSION))]));

        if ($berkas->isEmpty()) {
            $this->warn('Tidak ada berkas yang cocok di disk content'.($folder ? " (folder: {$folder})" : '').'.');

            return self::SUCCESS;
        }

        // Satu kueri untuk seluruh path yang sudah terdaftar, bukan satu kueri
        // per berkas: pada 249 berkas bedanya 249 kueri melawan satu.
        $sudahAda = Media::whereIn('path', $berkas->all())->pluck('path')->flip();

        $baru = $berkas->reject(fn (string $path) => $sudahAda->has($path));

        $this->info(sprintf(
            '%d berkas di disk, %d sudah terdaftar, %d akan ditambahkan.',
            $berkas->count(), $sudahAda->count(), $baru->count(),
        ));

        if ($baru->isEmpty()) {
            return self::SUCCESS;
        }

        if ($dry) {
            $baru->take(20)->each(fn (string $path) => $this->line("  {$path}"));
            if ($baru->count() > 20) {
                $this->line(sprintf('  … dan %d lagi', $baru->count() - 20));
            }

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($baru->count());

        foreach ($baru as $path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            Media::create([
                'disk' => 'content',
                'path' => $path,
                'original_name' => basename($path),
                'mime_type' => self::JENIS[$ext],
                'size' => $disk->size($path),
                // Folder diambil dari letak berkasnya, bukan diketik ulang.
                // Berkas di akar disk tidak berfolder, dan itu keadaan yang sah.
                'folder' => str_contains($path, '/') ? dirname($path) : null,
                'title' => null,
                'alt_text' => null,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("{$baru->count()} berkas didaftarkan.");
        $this->comment('Alt text masih kosong. Lihat penyaring "Belum ada alt text" di Pustaka Media.');

        return self::SUCCESS;
    }
}
