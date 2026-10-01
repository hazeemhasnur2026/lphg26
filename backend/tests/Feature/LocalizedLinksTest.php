<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi utama dari App\Support\Localization: pengunjung yang membuka
 * halaman /en/... tidak boleh terlempar balik ke URL tanpa prefiks begitu
 * mengeklik apa pun. Sebelum @lurl() dipasang, setiap href internal ditulis
 * polos ("/page/...") dan selalu menunjuk versi Indonesia.
 */
class LocalizedLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_in_english_with_translated_content(): void
    {
        $response = $this->get('/en')->assertOk();

        $response->assertSee('Seven Values We Hold');
        $response->assertSee('Started the river-transport business.', false);
        $response->assertSee('og:locale" content="en_GB"', false);
    }

    public function test_homepage_internal_links_stay_prefixed_with_en(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.url('/en/page/about-us/overview').'"', $html);
        $this->assertStringContainsString('href="'.url('/en').'"', $html);
    }

    public function test_static_page_breadcrumb_and_tabs_stay_prefixed_with_en(): void
    {
        $html = $this->get('/en/page/csr/csr-overview')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.url('/en').'"', $html);
    }

    public function test_default_locale_homepage_is_unaffected(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('Tujuh Nilai yang Kami Pegang');
        $response->assertSee('Mulai usaha angkutan sungai.', false);
        $response->assertSee('og:locale" content="id_ID"', false);
        $response->assertDontSee('/en/page');
    }
}
