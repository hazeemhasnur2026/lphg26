<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportScrapedArticlesTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = storage_path('framework/testing/raw');
        File::ensureDirectoryExists($this->path);

        File::put($this->path.'/bertanggal.json', json_encode([
            'slug' => 'bertanggal', 'title' => 'Bertanggal',
            'category' => 'latest-update', 'content' => '<p>isi</p>',
            'publishedAt' => '2026-05-01',
        ]));
        File::put($this->path.'/tanpa-tanggal.json', json_encode([
            'slug' => 'tanpa-tanggal', 'title' => 'Tanpa Tanggal',
            'category' => 'latest-update', 'content' => '<p>isi</p>',
        ]));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->path);

        parent::tearDown();
    }

    /**
     * Artikel tanpa tanggal dihapus permanen dan URL-nya dijawab 410. Impor
     * yang menghidupkannya kembali diam-diam akan bertabrakan dengan 410 itu.
     */
    public function test_undated_articles_are_skipped(): void
    {
        $this->artisan('import:articles', ['--path' => $this->path])->assertSuccessful();

        $this->assertDatabaseHas('posts', ['slug' => 'bertanggal']);
        $this->assertDatabaseMissing('posts', ['slug' => 'tanpa-tanggal']);
        $this->assertSame(1, Post::count());
    }

    public function test_the_decision_can_be_reversed_with_a_flag(): void
    {
        $this->artisan('import:articles', ['--path' => $this->path, '--with-undated' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('posts', ['slug' => 'tanpa-tanggal', 'published_at' => null]);
    }
}
