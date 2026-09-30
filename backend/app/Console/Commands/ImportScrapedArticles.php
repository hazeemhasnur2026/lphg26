<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Loads the articles pulled off the old site by scripts/crawl-articles.js into
 * the posts table.
 *
 * Runs on slug, so re-running updates rather than duplicating. The crawl can
 * be refreshed and re-imported without cleaning the table first.
 *
 * Artikel tanpa dateline **dilewati**, keputusan 2026-09-23: 26 artikel seperti
 * itu dihapus permanen dan URL-nya kini dijawab 410. Tanpa penyaringan ini,
 * impor berikutnya menghidupkan mereka kembali diam-diam dan bertabrakan dengan
 * 410 yang sudah diumumkan ke Google. Berkas crawl-nya tetap disimpan, jadi
 * --with-undated cukup untuk membatalkan keputusan itu.
 */
class ImportScrapedArticles extends Command
{
    protected $signature = 'import:articles
                            {--path= : folder content-migration/raw (default: ../content-migration/raw)}
                            {--dry-run : tampilkan hasil tanpa menulis ke database}
                            {--with-undated : ikut mengimpor artikel yang tidak punya tanggal terbit}';

    protected $description = 'Impor artikel hasil crawl situs lama ke tabel posts';

    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('../content-migration/raw');

        if (! File::isDirectory($path)) {
            $this->error("Folder tidak ditemukan: {$path}");
            $this->line('Jalankan dulu: node scripts/crawl-articles.js');

            return self::FAILURE;
        }

        $files = collect(File::allFiles($path))
            ->filter(fn ($f) => $f->getExtension() === 'json');

        if ($files->isEmpty()) {
            $this->error("Tidak ada berkas JSON di {$path}");

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $withUndated = (bool) $this->option('with-undated');
        $created = $updated = $skipped = 0;
        $undated = [];

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        foreach ($files as $file) {
            $data = json_decode(File::get($file->getPathname()), true);

            if (! is_array($data) || empty($data['slug']) || empty($data['title'])) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $attributes = [
                'title' => $data['title'],
                'category' => $data['category'] ?? 'uncategorised',
                'excerpt' => $data['excerpt'] ?? null,
                'body' => $data['content'] ?? '',
                // Disimpan relatif terhadap disk `content`; crawler menulisnya
                // dengan awalan "images/" yang merupakan bentuk foldernya, bukan
                // bagian dari identitas berkasnya.
                'cover_image' => isset($data['coverImage'])
                    ? ltrim(str_replace('images/', '', $data['coverImage']), '/')
                    : null,
                // Left null when the source had no dateline. Never invented,
                // the list is printed at the end for a human to fill in.
                'published_at' => $data['publishedAt'] ?? null,
                'seo_title' => $data['seo']['title'] ?? null,
                'source_url' => $data['sourceUrl'] ?? null,
                // Terbit, bukan draf.
                //
                // Kolom status punya nilai bawaan 'draft', dan mengandalkan
                // bawaan itu di sini menyalakan situs dalam keadaan kosong:
                // entrypoint Docker menjalankan importer ini saat container
                // pertama kali hidup, sehingga seluruh artikel hasil migrasi
                // akan mendarat sebagai draf dan tidak satu pun tampil.
                //
                // Artikel-artikel ini sudah terbit di situs lama dan sudah
                // terindeks Google. Memindahkannya ke sini tidak menariknya
                // kembali ke meja redaksi.
                'status' => PostStatus::Published,
            ];

            if (empty($attributes['published_at'])) {
                $undated[] = "{$attributes['category']}/{$data['slug']}";

                if (! $withUndated) {
                    $bar->advance();

                    continue;
                }
            }

            if (! $dry) {
                $post = Post::updateOrCreate(['slug' => $data['slug']], $attributes);
                $post->wasRecentlyCreated ? $created++ : $updated++;
            } else {
                Post::where('slug', $data['slug'])->exists() ? $updated++ : $created++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info($dry ? 'Uji coba (tidak ada yang ditulis)' : 'Impor selesai');
        $this->table(
            ['baru', 'diperbarui', 'dilewati', 'total berkas'],
            [[$created, $updated, $skipped, $files->count()]]
        );

        if ($undated) {
            $this->newLine();
            $this->warn(count($undated).' artikel tanpa tanggal terbit, '
                .($withUndated ? 'tetap diimpor (--with-undated):' : 'dilewati, URL-nya dijawab 410:'));
            foreach (array_slice($undated, 0, 10) as $slug) {
                $this->line("  - {$slug}");
            }
            if (count($undated) > 10) {
                $this->line('  … dan '.(count($undated) - 10).' lagi');
            }
        }

        if (! $dry) {
            $this->newLine();
            $this->line('Per kategori:');
            foreach (Post::selectRaw('category, count(*) as n')->groupBy('category')->orderBy('category')->get() as $row) {
                $this->line(sprintf('  %-16s %d', $row->category, $row->n));
            }
        }

        return self::SUCCESS;
    }
}
