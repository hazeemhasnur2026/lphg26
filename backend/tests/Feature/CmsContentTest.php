<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Office;
use App\Models\Post;
use App\Models\SlideBanner;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Konten yang dulu ditulis langsung di Blade sekarang datang dari database.
 *
 * Yang diuji di sini bukan "halaman balas 200", itu tetap terjadi walau
 * datanya diabaikan. Yang diuji: isi halaman benar-benar berubah mengikuti
 * database, karena itulah satu-satunya alasan pekerjaan ini ada.
 */
class CmsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_cards_come_from_the_business_units_table(): void
    {
        BusinessUnit::factory()->create([
            'name' => 'Unit Percobaan',
            'slug' => 'unit-percobaan',
            'short_description' => 'Deskripsi dari database.',
            'tint_color' => '#ABCDEF',
            'accent_color' => '#123456',
        ]);

        $response = $this->get('/');

        $response->assertSee('Unit Percobaan');
        $response->assertSee('Deskripsi dari database.');
        $response->assertSee('/page/our-business/unit-percobaan');
        // Warna ikut dari database: menambah SBU dari admin tidak boleh
        // menghasilkan kartu tanpa warna.
        $response->assertSee('background:#ABCDEF', false);
        $response->assertSee('background:#123456', false);
    }

    public function test_first_four_cards_are_narrower_than_the_rest(): void
    {
        $units = BusinessUnit::factory()->count(5)->sequence(
            fn ($sequence) => ['order' => $sequence->index]
        )->create();

        $html = $this->get('/')->getContent();

        // 4 lalu 3, sesuai aset rujukan. Turunan urutan, bukan kolom database.
        $this->assertSame(4, substr_count($html, 'xl:col-span-3'));
        $this->assertSame(1, substr_count($html, 'xl:col-span-4'));
        $this->assertCount(5, $units);
    }

    public function test_homepage_slider_reads_active_slides_in_order(): void
    {
        SlideBanner::factory()->create(['title' => 'Slide Kedua', 'order' => 1]);
        SlideBanner::factory()->create(['title' => 'Slide Pertama', 'order' => 0]);
        SlideBanner::factory()->inactive()->create(['title' => 'Slide Nonaktif']);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('Slide Pertama', $html);
        $this->assertStringContainsString('Slide Kedua', $html);
        $this->assertStringNotContainsString('Slide Nonaktif', $html);
        $this->assertLessThan(
            strpos($html, 'Slide Kedua'),
            strpos($html, 'Slide Pertama'),
            'Slide harus terurut menurut kolom order.',
        );
    }

    /**
     * Slider memilih varian WebP saat render, bukan menyimpannya di kolom
     * image. Kolom itu tetap berisi gambar master yang diunggah orang.
     */
    public function test_slider_serves_a_webp_variant_but_stores_the_master(): void
    {
        $master = '/assets/img/gallery/01-hasnur-group-she-activity.jpg';
        SlideBanner::factory()->create(['image' => $master, 'title' => 'Slide Uji']);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('img-opt', $html);
        $this->assertDatabaseHas('slide_banners', ['image' => $master]);
    }

    public function test_footer_addresses_come_from_the_offices_table(): void
    {
        Office::factory()->headquarters()->create([
            'name' => 'Kantor Pusat Uji',
            'address' => "Baris Pertama\nBaris Kedua",
        ]);
        Office::factory()->create(['name' => 'Perwakilan Uji']);

        $response = $this->get('/');

        $response->assertSee('Kantor Pusat Uji');
        $response->assertSee('Baris Pertama');
        // Baris kedua alamat dipisah <br>, bukan hilang.
        $response->assertSee('Baris Kedua');
        $response->assertSee('Perwakilan Uji');
    }

    /**
     * Tiga judul di footer dulu ditulis tangan dan tidak satu pun cocok dengan
     * artikel di database, sementara tautannya menuju halaman daftar.
     */
    public function test_footer_recent_posts_link_to_real_articles(): void
    {
        $post = Post::factory()->create(['title' => 'Artikel Nyata', 'slug' => 'artikel-nyata']);

        $response = $this->get('/');

        $response->assertSee('Artikel Nyata');
        $response->assertSee($post->url, false);
    }

    /**
     * Tautan ke situs tiap SBU datang dari database, dan tombolnya hanya ada
     * kalau alamatnya benar-benar diisi. Tombol yang mengarah ke mana-mana
     * lebih buruk daripada tidak ada tombol.
     */
    public function test_business_unit_page_links_to_its_own_website_when_set(): void
    {
        BusinessUnit::factory()->create(['slug' => 'energy', 'name' => 'Energi']);

        $this->get('/page/our-business/energy')
            ->assertOk()
            ->assertDontSee('Kunjungi Situs');

        BusinessUnit::where('slug', 'energy')->update(['website_url' => 'https://contoh.example.com']);

        $this->get('/page/our-business/energy')
            ->assertOk()
            ->assertSee('Kunjungi Situs')
            ->assertSee('Situs resmi Energi')
            ->assertSee('https://contoh.example.com', false);
    }

    /**
     * Roda nilai inti pindah dari menu Tentang Kami ke beranda, di antara hero
     * dan galeri kegiatan. Halaman rincinya tetap ada. URL-nya sudah terindeks.
     */
    public function test_core_values_sit_on_the_homepage_between_hero_and_gallery(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('core-value-wheel', $html);
        $this->assertLessThan(
            strpos($html, 'Galeri Kegiatan'),
            strpos($html, 'Tujuh Nilai yang Kami Pegang'),
            'Nilai inti harus berada di atas galeri kegiatan.',
        );

        // Ketujuh nilai hanya ada sebagai gambar; daftar teksnya yang membuatnya
        // terbaca pembaca layar dan mesin pencari.
        foreach (['Pantang Menyerah', 'Keadilan', 'Kebersamaan', 'Bijaksana',
            'Kesatuan Sikap', 'Dapat Dipercaya', 'Disiplin'] as $value) {
            $this->assertStringContainsString($value, $html);
        }
    }

    /**
     * Halaman Nilai Inti tersendiri dihapus karena isinya pindah ke beranda.
     * URL-nya sudah terindeks sejak situs lama, jadi dijawab 301. Isinya tidak
     * hilang, hanya berpindah, dan itulah arti pengalihan permanen.
     */
    public function test_the_old_core_value_page_redirects_to_the_homepage(): void
    {
        $this->get('/page/about-us/core-value')
            ->assertStatus(301)
            // url('/') tidak membawa garis miring penutup; beranda membawanya.
            ->assertRedirect(url('/').'/#nilai-inti');

        // Pengalihannya mengikuti bahasa, bukan melempar pembaca /en kembali
        // ke beranda Indonesia.
        $this->get('/en/page/about-us/core-value')
            ->assertStatus(301)
            ->assertRedirect(url('/en').'#nilai-inti');
    }

    public function test_the_core_value_cards_are_in_the_page_source(): void
    {
        // Kartunya disembunyikan oleh skrip saat digulir, tapi selalu ada di
        // HTML. Kalau hanya dibuat saat dibutuhkan, isinya tidak terbaca mesin
        // pencari dan pembaca layar. Kelas penyembunyinya pun baru dipasang
        // setelah skrip menyatakan dirinya berjalan.
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Keserasian pandangan, keyakinan dan tindakan', $html);
        $this->assertStringNotContainsString('core-value-scripted', $html);
        // Warna kartu diambil dari piksel pita roda, bukan dicocokkan dengan mata.
        $this->assertStringContainsString('background:#DA272C', $html);
        $this->assertSame(7, substr_count($html, 'core-value-card rounded-2xl'));
    }

    public function test_vacancy_page_says_so_when_nothing_is_open(): void
    {
        $this->get('/page/career/our-vacancy')
            ->assertOk()
            ->assertSee('belum ada posisi yang dibuka');
    }

    public function test_vacancy_page_lists_open_roles_only(): void
    {
        Vacancy::factory()->create(['title' => 'Operator Alat Berat']);
        Vacancy::factory()->closed()->create(['title' => 'Posisi Ditutup']);
        Vacancy::factory()->expired()->create(['title' => 'Posisi Kedaluwarsa']);

        $response = $this->get('/page/career/our-vacancy');

        $response->assertSee('Operator Alat Berat');
        $response->assertDontSee('Posisi Ditutup');
        $response->assertDontSee('Posisi Kedaluwarsa');
        $response->assertDontSee('belum ada posisi yang dibuka');
    }

    public function test_seeders_carry_the_content_that_was_hardcoded(): void
    {
        $this->seed();

        $this->assertSame(7, BusinessUnit::count());
        $this->assertSame(3, Office::count());
        $this->assertSame(5, SlideBanner::count());
        // Lowongan sengaja tidak di-seed: situs lama tidak punya satu pun.
        $this->assertSame(0, Vacancy::count());

        // Nama SBU tersimpan sebagai karakter sebenarnya, bukan entitas HTML.
        // name kolom JSON translatable sekarang, jadi dicek lewat model
        // (accessor), bukan assertDatabaseHas yang membandingkan kolom mentah.
        $this->assertTrue(
            BusinessUnit::where('name->id', 'Agribisnis & Kehutanan')->exists()
        );
    }
}
