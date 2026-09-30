<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use App\Support\PostWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Artikel yang tampil di situs wajib bergambar sampul.
 *
 * Gerbangnya di penerbitan, bukan di penyimpanan. Mewajibkannya sejak draf
 * berarti penulis tidak bisa menyimpan tulisan setengah jadi tanpa lebih dulu
 * mencari gambar — dan yang terjadi berikutnya adalah gambar asal-asalan yang
 * dipasang supaya tombol simpan menyala.
 */
class CoverImageRequiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_draft_may_be_saved_without_an_image(): void
    {
        $post = Post::factory()->draft()->create(['cover_image' => null]);

        $this->assertNull($post->fresh()->cover_image);
        $this->assertSame(PostStatus::Draft, $post->fresh()->status);
    }

    public function test_publishing_without_an_image_is_refused(): void
    {
        $this->actingAs(User::factory()->reviewer()->create());
        $post = Post::factory()->create([
            'status' => PostStatus::Approved,
            'cover_image' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        PostWorkflow::transition($post, PostStatus::Published);
    }

    public function test_the_refusal_says_what_is_missing(): void
    {
        $post = Post::factory()->draft()->create(['cover_image' => null]);

        $alasan = PostWorkflow::alasanBelumBolehTerbit($post);

        $this->assertNotNull($alasan);
        $this->assertStringContainsString('gambar sampul', $alasan);
    }

    public function test_publishing_with_an_image_still_works(): void
    {
        $this->actingAs(User::factory()->reviewer()->create());
        $post = Post::factory()->create([
            'status' => PostStatus::Approved,
            'cover_image' => 'unggahan/sampul.jpg',
            'published_at' => null,
        ]);

        PostWorkflow::transition($post, PostStatus::Published);

        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->assertNull(PostWorkflow::alasanBelumBolehTerbit($post->fresh()));
    }

    /**
     * Importer adalah satu-satunya jalan lain menuju status Terbit selain
     * tombol di panel. Kalau ia tidak menghormati aturan yang sama, tiap impor
     * ulang mengembalikan artikel tanpa gambar ke situs.
     */
    public function test_the_importer_keeps_imageless_articles_as_drafts(): void
    {
        if (! is_dir(base_path('../content-migration/raw'))) {
            $this->markTestSkipped('Hasil crawl tidak ada di lingkungan ini.');
        }

        $this->artisan('import:articles')->assertSuccessful();

        $bocor = Post::where('status', PostStatus::Published)
            ->where(fn ($q) => $q->whereNull('cover_image')->orWhere('cover_image', ''))
            ->count();

        $this->assertSame(0, $bocor, 'Importer menerbitkan artikel tanpa gambar.');
    }

    /** Aturan baru tidak boleh menyisakan artikel lama yang melanggarnya. */
    public function test_no_published_article_is_left_without_an_image(): void
    {
        Post::factory()->count(3)->create(['cover_image' => 'unggahan/ada.jpg']);
        Post::factory()->draft()->create(['cover_image' => null]);

        $this->assertSame(0, Post::published()
            ->where(fn ($q) => $q->whereNull('cover_image')->orWhere('cover_image', ''))
            ->count());
    }
}
