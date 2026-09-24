<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perbaikan tingkat P0 dari audit 24 September 2026.
 *
 * Empat hal di bawah gampang hilang tanpa disadari: header dicabut saat
 * menyederhanakan middleware, tautan kebijakan hilang saat footer diubah, dan
 * pembatasan unggahan lenyap saat form disusun ulang. Halamannya tetap 200 di
 * setiap kasus itu, jadi hanya tes yang menahannya.
 */
class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_response_carries_the_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Content-Security-Policy-Report-Only');
    }

    /**
     * HSTS hanya boleh dikirim lewat HTTPS. Mengirimnya di http membuat peramban
     * pengembang mengunci domain itu ke https selama setahun.
     */
    public function test_hsts_is_withheld_on_plain_http(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_the_privacy_policy_is_reachable_and_linked(): void
    {
        $this->get('/page/legal/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            // Bagian yang menyatakan apa yang benar-benar dikumpulkan.
            ->assertSee('alamat IP');

        // Tertaut dari footer, jadi ada di tiap halaman.
        $this->get('/')->assertSee('/page/legal/kebijakan-privasi', false);

        // Dan di titik pengumpulan datanya.
        Office::factory()->headquarters()->create();
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('/page/legal/kebijakan-privasi', false);
    }

    public function test_the_privacy_policy_is_listed_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(url('/page/legal/kebijakan-privasi'), false);
    }

    /**
     * Filament menerjemahkan ->image() menjadi image/*, yang mencakup SVG. SVG
     * yang dilayani dari domain yang sama dapat menjalankan skrip.
     *
     * Diperiksa dari halaman yang benar-benar dirender, bukan dari objek form:
     * yang menentukan perilaku unggahan adalah konfigurasi yang sampai ke
     * peramban.
     */
    public function test_uploads_reject_svg_and_oversized_files(): void
    {
        $this->actingAs(User::factory()->create());

        $html = html_entity_decode($this->get('/admin/posts/create')->assertOk()->getContent());
        $html = str_replace(['\\/', '\u0022'], ['/', '"'], $html);

        foreach (['image/jpeg', 'image/png', 'image/webp'] as $tipe) {
            $this->assertStringContainsString($tipe, $html);
        }

        // image/* adalah nilai bawaan ->image() dan mencakup SVG.
        $this->assertStringNotContainsString('"image/*"', $html);
        $this->assertStringContainsString("maxSize: '4096KB'", $html);
    }
}
