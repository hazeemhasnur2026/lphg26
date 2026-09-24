<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hal-hal yang menentukan apakah cutover merusak posisi di Google.
 */
class LaunchTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_covers_pages_listings_and_articles(): void
    {
        $post = Post::factory()->create([
            'slug' => 'artikel-uji',
            'category' => 'latest-update',
            'published_at' => '2026-09-01',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(url('/'), false);
        $response->assertSee(url('/page/about-us/overview'), false);
        $response->assertSee(url('/page/publication/latest-update'), false);
        $response->assertSee(url("/post/latest-update/{$post->slug}"), false);
        $response->assertSee('<lastmod>2026-09-01</lastmod>', false);
    }

    public function test_sitemap_leaves_out_press_coverage_we_do_not_host(): void
    {
        Post::factory()->citation()->create(['slug' => 'liputan-antara']);

        $response = $this->get('/sitemap.xml');

        // Kutipan menunjuk ke penerbitnya; tidak ada halaman kami untuk diindeks.
        $response->assertDontSee('antaranews.com', false);
        $response->assertDontSee(url('/post/news/liputan-antara'), false);
    }

    /**
     * 26 URL artikel sudah terindeks tapi artikelnya dihapus permanen. 410
     * memberi tahu Google URL-nya memang sudah tidak ada; 404 dianggap mungkin
     * sementara, dan pengalihan ke daftar kategori dinilai soft 404.
     */
    public function test_retired_article_urls_answer_410_gone(): void
    {
        $response = $this->get('/post/she-activity/environmental-management-and-monitoring');

        $response->assertStatus(410);
        // Pengunjungnya datang dari hasil pencarian, jadi halamannya harus
        // menjelaskan keadaan, bukan halaman error bawaan kerangka kerja.
        $response->assertSee('sudah tidak diterbitkan');
        $response->assertSee('name="robots" content="noindex"', false);
    }

    public function test_the_gone_page_offers_the_listing_only_when_it_has_articles(): void
    {
        $gone = '/post/she-activity/environmental-management-and-monitoring';

        // Diuji lewat label tombolnya: tautan ke kategori ini juga ada di
        // navigasi header setiap halaman, jadi mencari URL-nya tidak memisahkan
        // keduanya.
        $this->get($gone)->assertDontSee('Lihat Aktivitas SHE');

        Post::factory()->create(['category' => 'she-activity']);

        $this->get($gone)->assertSee('Lihat Aktivitas SHE');
    }

    public function test_empty_listings_stay_out_of_the_sitemap(): void
    {
        Post::factory()->create(['category' => 'latest-update']);

        $response = $this->get('/sitemap.xml');

        // Daftar kosong berisiko dinilai soft 404, jadi tidak diajukan…
        $response->assertDontSee(url('/page/she/she-activity'), false);
        // …tapi halamannya tetap bisa dibuka dan memberi tahu keadaannya.
        $this->get('/page/she/she-activity')
            ->assertOk()
            ->assertSee('Belum ada publikasi');
    }

    public function test_urls_that_never_existed_still_404(): void
    {
        $this->get('/post/news/slug-yang-tidak-pernah-ada')->assertNotFound();
    }

    public function test_live_articles_are_not_redirected(): void
    {
        Post::factory()->create([
            'slug' => 'artikel-hidup',
            'category' => 'latest-update',
        ]);

        $this->get('/post/latest-update/artikel-hidup')->assertOk();
    }
}
