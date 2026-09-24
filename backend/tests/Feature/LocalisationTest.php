<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalisationTest extends TestCase
{
    use RefreshDatabase;

    public function test_indonesian_stays_at_the_root_url(): void
    {
        // Setiap tautan yang sudah terindeks Google memakai bentuk tanpa
        // prefiks. Memberinya /id akan memutus semuanya sekaligus.
        //
        // Yang diperiksa bukan sekadar status 200: kalau bahasa default tergeser
        // ke Inggris. Misalnya karena APP_LOCALE di .env server tertinggal di
        // nilai bawaan Laravel. Alamat ini tetap 200, hanya isinya yang
        // berganti bahasa dan versi Indonesianya pindah ke /id.
        $this->assertSame('id', config('app.locale'), 'Bahasa default harus Indonesia.');

        $this->get('/page/about-us/overview')
            ->assertOk()
            ->assertSee('<html lang="id"', false);

        $this->get('/')->assertOk()->assertSee('<html lang="id"', false);

        // Dan versi Indonesia tidak boleh punya alamat berprefiks.
        $this->get('/id/page/about-us/overview')->assertNotFound();
    }

    public function test_english_and_japanese_have_their_own_urls(): void
    {
        foreach (['en', 'ja'] as $locale) {
            $this->get("/{$locale}/page/about-us/overview")
                ->assertOk()
                ->assertSee('<html lang="'.$locale.'"', false);
        }
    }

    public function test_navigation_is_translated(): void
    {
        $this->get('/')->assertSee('Tentang Kami');
        $this->get('/en')->assertSee('About Us');
        $this->get('/ja')->assertSee('会社概要');
    }

    /**
     * hreflang memberi tahu Google bahwa ketiga alamat itu versi bahasa dari
     * halaman yang sama, bukan tiga halaman yang saling bersaing.
     */
    public function test_every_page_declares_its_language_alternates(): void
    {
        $response = $this->get('/en/page/about-us/overview')->assertOk();

        $response->assertSee('hreflang="id-ID" href="'.url('/page/about-us/overview').'"', false);
        $response->assertSee('hreflang="en-GB" href="'.url('/en/page/about-us/overview').'"', false);
        $response->assertSee('hreflang="ja-JP" href="'.url('/ja/page/about-us/overview').'"', false);
        // x-default menunjuk versi Indonesia, bahasa asli situs ini.
        $response->assertSee('hreflang="x-default" href="'.url('/page/about-us/overview').'"', false);
    }

    /**
     * Canonical yang salah di sini lebih berbahaya daripada tidak ada canonical:
     * /en yang menunjuk versi Indonesia menyatakan dirinya duplikat, dan Google
     * mengeluarkannya dari indeks.
     */
    public function test_each_language_is_its_own_canonical(): void
    {
        $post = Post::factory()->create(['slug' => 'artikel-uji', 'category' => 'latest-update']);

        $this->get('/en')->assertSee('<link rel="canonical" href="'.url('/en').'"', false);
        $this->get('/ja')->assertSee('<link rel="canonical" href="'.url('/ja').'"', false);

        $this->get("/en/post/latest-update/{$post->slug}")
            ->assertSee('<link rel="canonical" href="'.url("/en/post/latest-update/{$post->slug}").'"', false);
    }

    public function test_the_sitemap_lists_every_page_in_every_language(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        $response->assertSee(url('/page/about-us/overview'), false);
        $response->assertSee(url('/en/page/about-us/overview'), false);
        $response->assertSee(url('/ja/page/about-us/overview'), false);
        // Versi Indonesia tidak boleh ditulis /id/..., itu alamat yang dialihkan.
        $response->assertDontSee(url('/id/page/about-us/overview'), false);
    }

    public function test_the_switcher_offers_all_three_languages(): void
    {
        $response = $this->get('/')->assertOk();

        // Nama bahasa ditulis dalam bahasanya sendiri: yang mencari bahasanya
        // belum tentu bisa membaca bahasa halaman saat ini.
        $response->assertSee('Bahasa Indonesia');
        $response->assertSee('English');
        $response->assertSee('日本語');
    }

    /**
     * Tombolnya ikon globe saja. Navbar sempat meluber sekitar 180px di layar
     * 1024px, dan kode bahasa yang menempel di bilah navigasi ikut menyumbang.
     */
    public function test_the_switcher_button_is_an_icon_with_an_accessible_name(): void
    {
        $response = $this->get('/en')->assertOk();

        $response->assertSee('title="Language"', false);
        $response->assertSee('<span class="sr-only">Language</span>', false);
        // Kode bahasa tidak lagi ditempel di bilah navigasi.
        $response->assertDontSee('>EN<', false);
    }
}
