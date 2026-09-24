<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Records a piece of press coverage as a citation.
 *
 * Deliberately manual. Harvesting article text from news sites and serving it
 * from our own domain would infringe the publisher's copyright. ANTARA, for
 * example, forbids automated collection in its terms on every article page.
 * A headline, outlet, date and link are facts about the coverage and are what
 * an "in the media" listing actually needs.
 */
class AddPressCoverage extends Command
{
    protected $signature = 'news:add
                            {--title= : judul berita}
                            {--url= : tautan ke artikel asli}
                            {--source= : nama media}
                            {--date= : tanggal terbit (YYYY-MM-DD)}
                            {--excerpt= : ringkasan singkat, tulis sendiri}';

    protected $description = 'Catat liputan media sebagai rujukan (bukan salinan isinya)';

    public function handle(): int
    {
        $title = $this->option('title') ?: $this->ask('Judul berita');
        $url = $this->option('url') ?: $this->ask('Tautan artikel asli');
        $source = $this->option('source') ?: $this->ask('Nama media');
        $date = $this->option('date') ?: $this->ask('Tanggal terbit (YYYY-MM-DD)');
        $excerpt = $this->option('excerpt') ?: $this->ask('Ringkasan singkat (opsional)', '');

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Tautan tidak valid.');

            return self::FAILURE;
        }

        if ($date && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->error('Format tanggal harus YYYY-MM-DD.');

            return self::FAILURE;
        }

        $post = Post::updateOrCreate(
            ['slug' => Str::slug($title)],
            [
                'title' => $title,
                'category' => 'news',
                'excerpt' => $excerpt ?: null,
                // Intentionally empty: we cite the coverage, we do not host it.
                'body' => '',
                'published_at' => $date ?: null,
                'external_url' => $url,
                'source_name' => $source,
            ]
        );

        $this->info(($post->wasRecentlyCreated ? 'Ditambahkan' : 'Diperbarui').": {$post->title}");
        $this->line("  {$source} · ".($date ?: 'tanpa tanggal'));
        $this->line("  {$url}");

        return self::SUCCESS;
    }
}
