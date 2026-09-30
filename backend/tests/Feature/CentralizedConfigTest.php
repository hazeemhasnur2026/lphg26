<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Vacancy;
use App\Support\StructuredData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Nilai yang sebelumnya ditulis ulang di beberapa tempat sekarang punya satu
 * sumber di config, dan mengubah nilainya di satu tempat harus terlihat di
 * semua tempat yang memakainya.
 */
class CentralizedConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * M-2: tautan sosial media dulu ditulis ulang di footer, StructuredData,
     * dan SeedAnalyticsSample — satu di antaranya sudah mulai berselisih
     * ("hasnur-group" dengan tanda hubung).
     */
    public function test_social_links_come_from_one_config_value(): void
    {
        config(['site.sosial.linkedin' => 'https://www.linkedin.com/company/uji-konsisten']);

        $this->get('/')->assertOk()->assertSee('uji-konsisten', false);

        $skema = StructuredData::organization();
        $this->assertStringContainsString('uji-konsisten', $skema);
    }

    /**
     * M-3: pita gradien merek disalin identik di tiga berkas. Diuji lewat
     * halaman artikel, yang memakai <x-brand-strip /> lewat posts/show.
     */
    public function test_brand_strip_renders_on_an_article_page(): void
    {
        $post = Post::factory()->create(['slug' => 'uji-pita-merek', 'category' => 'news']);

        $this->get("/post/news/{$post->slug}")->assertOk()
            ->assertSee('linear-gradient(90deg,#0F2747,#C9A227,#007A53,#E03633,#0F2747)', false);
    }

    /**
     * M-4: alamat surel rekrutmen ditulis tiga kali langsung di halaman
     * Lowongan, tidak lewat config sama sekali.
     */
    public function test_recruitment_email_comes_from_config(): void
    {
        config(['mail.recruitment_recipient' => 'karir-uji@contoh.test']);

        Vacancy::factory()->create(['is_open' => true]);

        // Tiga kemunculan di halaman yang sama, semuanya harus mengikuti
        // nilai config, bukan lagi literal recruitment@hasnurgroup.com.
        $html = $this->get('/page/career/our-vacancy')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($html, 'karir-uji@contoh.test'));
        $this->assertStringNotContainsString('recruitment@hasnurgroup.com', $html);
    }
}
