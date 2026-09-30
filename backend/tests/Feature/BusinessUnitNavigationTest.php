<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menu navigasi "Bisnis Kami" dibaca dari tabel business_units, bukan ditulis
 * tangan.
 *
 * Sebelumnya tujuh tautan ditulis manual, disalin identik untuk versi mobile
 * di berkas yang sama. Cocok hari ini karena kebetulan ada tepat tujuh unit
 * bisnis; menambah atau menghapus unit lewat panel tidak mengubah menu sama
 * sekali.
 */
class BusinessUnitNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_newly_added_business_unit_appears_in_the_nav(): void
    {
        BusinessUnit::factory()->create(['name' => 'Unit Percobaan', 'slug' => 'unit-percobaan', 'order' => 99]);

        $this->get('/')->assertOk()->assertSee('unit-percobaan', false);
    }

    public function test_a_removed_business_unit_disappears_from_the_nav(): void
    {
        $unit = BusinessUnit::factory()->create(['name' => 'Akan Dihapus', 'slug' => 'akan-dihapus']);
        $unit->delete();

        $this->get('/')->assertOk()->assertDontSee('akan-dihapus', false);
    }

    /**
     * Slug dan kunci terjemahan tidak selalu sama bentuknya
     * ("agribusiness-forestry" vs kunci "nav.business.agribusiness"). Label
     * di menu navigasi harus mengikuti pemetaan, bukan $unit->name.
     *
     * Beranda merender $unit->name mentah dua kali dalam satu halaman: sekali
     * di kartu SBU (rute /, langsung dari BusinessUnit::ordered() — perilaku
     * yang sudah benar dan sudah ada sebelum perubahan ini, karena kartu itu
     * belum multibahasa di seluruh locale), dan seharusnya TIDAK sekali lagi
     * di menu navigasi header, yang sekarang memakai pemetaan terjemahan.
     * Diperiksa dengan menghitung kemunculannya: harus tepat satu, bukan nol
     * (berarti label hilang total) dan bukan dua (berarti menu navigasi ikut
     * menampilkan nama mentah).
     */
    public function test_units_with_mapped_translation_keys_show_the_translated_label(): void
    {
        BusinessUnit::factory()->create([
            'name' => 'Nama Mentah Basis Data',
            'slug' => 'agribusiness-forestry',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(e(__('nav.business.agribusiness')), $html);
        $this->assertSame(
            1,
            substr_count($html, 'Nama Mentah Basis Data'),
            'Nama mentah muncul bukan tepat sekali — seharusnya hanya di kartu SBU beranda, tidak di menu navigasi.',
        );
    }

    /**
     * Unit yang belum didaftarkan di pemetaan jatuh ke $unit->name, bukan
     * pecah atau menunjuk kunci terjemahan unit lain.
     */
    public function test_an_unmapped_unit_falls_back_to_its_raw_name(): void
    {
        BusinessUnit::factory()->create([
            'name' => 'Unit Belum Dipetakan',
            'slug' => 'unit-belum-terpetakan',
        ]);

        $this->get('/')->assertOk()->assertSee('Unit Belum Dipetakan');
    }

    public function test_the_nav_reflects_the_ordered_scope(): void
    {
        BusinessUnit::query()->delete();
        BusinessUnit::factory()->create(['name' => 'Kedua', 'slug' => 'kedua', 'order' => 2]);
        BusinessUnit::factory()->create(['name' => 'Pertama', 'slug' => 'pertama', 'order' => 1]);

        $halaman = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($halaman, 'our-business/kedua'),
            strpos($halaman, 'our-business/pertama'),
            'Urutan menu tidak mengikuti kolom order.',
        );
    }
}
