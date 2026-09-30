<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Footer panel admin.
 */
class PanelFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_line_appears_in_the_panel(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk()
            ->assertSee('HASNUR GROUP — MANAGEMENT INFORMATION SYSTEMS & TECHNOLOGY');
    }

    /**
     * Tahun diambil dari jam, bukan ditulis tetap. Angka hak cipta yang
     * ditanam di kode selalu basi persis pada 1 Januari, dan tidak ada yang
     * memeriksanya sampai ada yang menyadari panelnya masih mengaku tahun lalu.
     */
    public function test_the_copyright_year_follows_the_clock(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->travelTo(now()->addYears(3));

        // Entitas, bukan karakternya: view menulis &copy; dan HTML keluaran
        // memuat entitas itu apa adanya.
        $this->get('/admin')->assertOk()->assertSee('&copy; '.now()->year, false);
    }

    public function test_both_links_are_present(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk()
            ->assertSee('Dukungan')
            ->assertSee('Kebijakan Privasi')
            ->assertSee(config('site.panel.dukungan'), false)
            ->assertSee(config('site.panel.privasi'), false);
    }

    /**
     * Keduanya dapat diatur lewat .env: alamat tim yang menangani panel ini
     * berubah lebih sering daripada kodenya, dan perubahan alamat surel tidak
     * layak menuntut deploy.
     */
    public function test_the_links_can_be_changed_without_touching_code(): void
    {
        config([
            'site.panel.dukungan' => 'https://dukungan.contoh.test',
            'site.panel.privasi' => 'https://contoh.test/privasi',
        ]);

        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk()
            ->assertSee('https://dukungan.contoh.test', false)
            ->assertSee('https://contoh.test/privasi', false);
    }

    /**
     * Gayanya ikut sebagai <style>, bukan sebagai kelas Tailwind.
     *
     * Panel memuat stylesheet Filament sendiri, bukan app.css. Kelas seperti
     * text-center dan border-t tidak ada di dalamnya, dan ditulis begitu saja
     * mereka tidak berlaku sama sekali — footernya tampil sebagai HTML polos,
     * tanpa galat apa pun.
     */
    public function test_the_footer_carries_its_own_styling(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $halaman = $this->get('/admin')->assertOk()->getContent();

        $this->assertStringContainsString('.hg-footer', $halaman);
        $this->assertStringContainsString('text-align: center', $halaman);
        // Mode gelap ikut, memakai kelas yang dipasang Filament sendiri.
        $this->assertStringContainsString('.fi-theme-dark .hg-footer', $halaman);
    }
}
