<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Apa yang boleh dilihat pengunjung (CMS-03 dan CMS-07).
 *
 * Bagian yang paling mudah salah dari seluruh pekerjaan ini: menyaring daftar
 * tapi lupa menyaring halaman artikelnya, sehingga draf tetap terbuka bagi
 * siapa pun yang menebak URL-nya. Draf yang bisa dibuka lewat URL bukan draf.
 */
class ContentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_draft_is_absent_from_the_listing_and_its_own_url(): void
    {
        $draf = Post::factory()->draft()->create([
            'title' => 'Naskah Belum Selesai',
            'category' => 'news',
            'slug' => 'naskah-belum-selesai',
        ]);

        $this->get('/page/publication/news')
            ->assertOk()
            ->assertDontSee('Naskah Belum Selesai');

        $this->get("/post/news/{$draf->slug}")->assertNotFound();
    }

    public function test_a_scheduled_article_stays_hidden_until_its_time(): void
    {
        $post = Post::factory()->scheduled()->create([
            'title' => 'Terbit Minggu Depan',
            'category' => 'news',
            'slug' => 'terbit-minggu-depan',
        ]);

        $this->get("/post/news/{$post->slug}")->assertNotFound();

        // Waktunya tiba tanpa ada yang menekan apa pun.
        $this->travel(8)->days();
        $this->get("/post/news/{$post->slug}")->assertOk()->assertSee('Terbit Minggu Depan');
    }

    public function test_an_article_stops_showing_after_its_unpublish_date(): void
    {
        $post = Post::factory()->create([
            'title' => 'Pengumuman Sementara',
            'category' => 'news',
            'slug' => 'pengumuman-sementara',
            'published_at' => now()->subDay(),
            'unpublish_at' => now()->addDay(),
        ]);

        $this->get("/post/news/{$post->slug}")->assertOk();

        $this->travel(2)->days();
        $this->get("/post/news/{$post->slug}")->assertNotFound();
    }

    /**
     * Artikel hasil migrasi yang tanggalnya belum dipastikan tetap tampil.
     *
     * Ini bukan kelalaian melainkan keputusan: 26 artikel itu sudah tampil sejak
     * situs menyala, dan menariknya karena kolom tanggalnya kosong berarti
     * menghapus 26 halaman dari situs tanpa seorang pun memutuskan begitu.
     */
    public function test_an_undated_article_is_still_public(): void
    {
        $post = Post::factory()->undated()->create([
            'title' => 'Artikel Tanpa Tanggal',
            'category' => 'news',
            'slug' => 'artikel-tanpa-tanggal',
        ]);

        $this->get("/post/news/{$post->slug}")->assertOk();
        $this->get('/page/publication/news')->assertSee('Artikel Tanpa Tanggal');
    }

    public function test_an_archived_article_leaves_the_site_but_keeps_its_content(): void
    {
        $post = Post::factory()->archived()->create([
            'category' => 'news',
            'slug' => 'ditarik',
            'body' => '<p>Isinya masih ada.</p>',
        ]);

        $this->get('/post/news/ditarik')->assertNotFound();
        $this->assertStringContainsString('Isinya masih ada.', $post->fresh()->body);
    }

    /**
     * Instalasi baru harus menyala dengan situs yang berisi.
     *
     * Entrypoint Docker menjalankan `import:articles` saat container pertama
     * kali hidup. Bila importer membiarkan kolom status memakai nilai
     * bawaannya, seluruh artikel hasil migrasi mendarat sebagai draf dan situs
     * menyala kosong — tanpa galat, dan tanpa ada yang menyadarinya sampai
     * seseorang membuka halaman Berita.
     */
    public function test_imported_articles_arrive_published_not_as_drafts(): void
    {
        $berkas = base_path('../content-migration/raw');

        if (! is_dir($berkas)) {
            $this->markTestSkipped('Hasil crawl tidak ada di lingkungan ini.');
        }

        $this->artisan('import:articles')->assertSuccessful();

        $this->assertGreaterThan(0, Post::published()->count(),
            'Importer menghasilkan nol artikel yang tampil di situs.');
        // Yang bergambar terbit; yang tidak bergambar sengaja tetap draf, sejak
        // aturan "artikel terbit wajib bergambar" berlaku. Yang diperiksa di
        // sini adalah tidak adanya artikel bergambar yang tertinggal draf.
        $tertinggal = Post::where('status', PostStatus::Draft)
            ->whereNotNull('cover_image')
            ->where('cover_image', '!=', '')
            ->count();

        $this->assertSame(0, $tertinggal, 'Artikel bergambar mendarat sebagai draf.');
    }

    public function test_the_sitemap_leaves_out_drafts_and_noindex_pages(): void
    {
        Post::factory()->create(['category' => 'news', 'slug' => 'tampil']);
        Post::factory()->draft()->create(['category' => 'news', 'slug' => 'draf']);
        Post::factory()->create(['category' => 'news', 'slug' => 'disembunyikan', 'noindex' => true]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/post/news/tampil', $xml);
        $this->assertStringNotContainsString('/post/news/draf', $xml);
        $this->assertStringNotContainsString('/post/news/disembunyikan', $xml);
    }
}
