<?php

namespace Tests\Feature;

use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengumuman yang muncul sekali saat pengunjung pertama kali membuka situs.
 */
class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_active_announcement_reaches_the_page(): void
    {
        Announcement::factory()->create(['title' => 'Libur Idul Fitri 1447 H']);

        $this->get('/')->assertOk()->assertSee('Libur Idul Fitri 1447 H');
    }

    public function test_nothing_is_rendered_when_there_is_none(): void
    {
        $this->get('/')->assertOk()->assertDontSee('aria-labelledby="judul-pengumuman"', false);
    }

    public function test_an_inactive_one_is_not_shown(): void
    {
        Announcement::factory()->nonaktif()->create(['title' => 'Belum Waktunya']);

        $this->get('/')->assertOk()->assertDontSee('Belum Waktunya');
    }

    public function test_scheduling_holds_it_back_and_then_releases_it(): void
    {
        $p = Announcement::factory()->terjadwal()->create(['title' => 'Mulai Pekan Depan']);

        $this->get('/')->assertOk()->assertDontSee('Mulai Pekan Depan');

        $this->travelTo($p->starts_at->addHour());
        $this->get('/')->assertOk()->assertSee('Mulai Pekan Depan');
    }

    public function test_an_expired_one_stops_showing(): void
    {
        Announcement::factory()->kedaluwarsa()->create(['title' => 'Sudah Lewat']);

        $this->get('/')->assertOk()->assertDontSee('Sudah Lewat');
    }

    /**
     * Dua pengumuman yang berlaku bersamaan adalah keadaan yang sah. Yang
     * menentukan mana yang tampil adalah urutannya, bukan kebetulan.
     */
    public function test_the_highest_order_wins_when_two_overlap(): void
    {
        Announcement::factory()->create(['title' => 'Yang Lama', 'order' => 1]);
        Announcement::factory()->create(['title' => 'Yang Utama', 'order' => 9]);

        $this->get('/')->assertOk()->assertSee('Yang Utama')->assertDontSee('Yang Lama');
    }

    /**
     * Isinya ditulis orang lewat editor kaya lalu dicetak dengan {!! !!} —
     * bentuk yang sama persis dengan celah XSS pada badan artikel.
     */
    public function test_the_body_is_sanitised(): void
    {
        Announcement::factory()->create([
            'title' => 'Pengumuman',
            'body' => '<p>Isi wajar.</p><script>window.dicuri=1</script><img src=x onerror="window.dicuri2=1">',
        ]);

        $halaman = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Isi wajar.', $halaman);
        $this->assertStringNotContainsString('window.dicuri', $halaman);
        $this->assertStringNotContainsString('onerror', $halaman);
    }

    /**
     * Penanda versi harus berubah tiap disunting, supaya pengumuman yang
     * diperbaiki muncul lagi kepada orang yang sudah menutupnya — yang sudah
     * mereka baca bukan lagi isi yang sekarang.
     */
    public function test_editing_makes_it_appear_again(): void
    {
        $p = Announcement::factory()->create(['title' => 'Versi Satu']);
        $sebelum = $p->signature;

        $this->travel(1)->minute();
        $p->update(['title' => 'Versi Dua']);

        $this->assertNotSame($sebelum, $p->fresh()->signature);
    }

    public function test_the_button_is_hidden_when_it_has_no_destination(): void
    {
        Announcement::factory()->create(['cta_label' => 'Selengkapnya', 'cta_url' => null]);

        $this->get('/')->assertOk()->assertDontSee('data-cta="pengumuman"', false);
    }

    public function test_the_image_and_its_alt_text_reach_the_popup(): void
    {
        Announcement::factory()->create([
            'image' => 'unggahan/pengumuman/contoh.jpg',
            'image_alt' => 'Kegiatan enam dekade',
        ]);

        $this->get('/')->assertOk()
            ->assertSee('unggahan/pengumuman/contoh.jpg', false)
            ->assertSee('Kegiatan enam dekade');
    }

    /**
     * Ornamen khas ikut, dan ikut sebagai hiasan.
     *
     * Key visual sudut dan pita merek membuat dialog ini terbaca sebagai bagian
     * situs, bukan jendela asing. Keduanya harus tersembunyi dari pembaca
     * layar: menyuarakan "gambar" untuk sesuatu yang tidak membawa isi hanya
     * menambah kebisingan bagi yang paling bergantung padanya.
     */
    /**
     * Gambar pengumuman tidak boleh dipotong.
     *
     * Versi pertama mengunci kotaknya 16:9 dengan object-cover, dan foto
     * pertama yang dipasang langsung kehilangan bagian atas kepala. Yang
     * mengunggah memilih gambarnya dengan alasan; bukan tugas dialog ini
     * menebak bagian mana yang boleh hilang.
     */
    public function test_the_image_is_never_cropped(): void
    {
        Announcement::factory()->create(['image' => 'unggahan/pengumuman/contoh.jpg']);

        $halaman = $this->get('/')->assertOk()->getContent();

        // Diperiksa pada tag gambarnya sendiri, bukan pada seluruh halaman:
        // object-cover dipakai secara sah di tempat lain, misalnya thumbnail
        // galeri di beranda. Memeriksa seluruh halaman akan gagal karena
        // pemakaian yang benar di tempat lain.
        preg_match('/<img[^>]*unggahan\/pengumuman\/contoh\.jpg[^>]*>/', $halaman, $m);

        $this->assertNotEmpty($m, 'Tag gambar pengumuman tidak ditemukan.');
        $this->assertStringContainsString('object-contain', $m[0]);
        $this->assertStringNotContainsString('object-cover', $m[0]);
        // w-auto, bukan w-full: dengan w-full kotak gambar tetap selebar kartu
        // walau gambarnya sempit, dan rasionya tidak lagi rasio gambar.
        $this->assertStringContainsString('w-auto', $m[0]);
    }

    public function test_the_house_ornament_is_decoration_not_content(): void
    {
        Announcement::factory()->create();

        $halaman = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('keyvisual-corner.png', $halaman);
        $this->assertMatchesRegularExpression(
            '/<img[^>]*keyvisual-corner\.png[^>]*>/',
            $halaman,
        );
        // Aset yang sama dipakai beranda, jadi yang diperiksa adalah atributnya.
        $this->assertMatchesRegularExpression(
            '/<img[^>]*keyvisual-corner\.png[^>]*alt=""[^>]*aria-hidden="true"|<img[^>]*alt=""[^>]*aria-hidden="true"[^>]*keyvisual-corner/',
            $halaman,
        );
    }

    /** Tanpa gambar, kepalanya diisi pola titik — bukan ruang kosong. */
    public function test_a_text_only_announcement_still_has_a_header(): void
    {
        Announcement::factory()->create(['image' => null]);

        $this->get('/')->assertOk()->assertSee('hero-pattern', false);
    }

    /**
     * Mode poster: selebaran yang teksnya sudah tercetak di gambar.
     *
     * Menampilkan judul dan isi di bawahnya mengulang hal yang sama dua kali,
     * dan yang kedua selalu kalah rapi.
     */
    public function test_poster_mode_shows_the_image_without_repeating_the_text(): void
    {
        Announcement::factory()->create([
            'title' => 'Waspada Penipuan Rekrutmen',
            'body' => '<p>Teks yang tidak perlu diulang.</p>',
            'show_text' => false,
            'image' => 'unggahan/pengumuman/poster.png',
            'image_alt' => 'Rekrutmen tidak dipungut biaya',
        ]);

        $halaman = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('unggahan/pengumuman/poster.png', $halaman);
        $this->assertStringNotContainsString('Teks yang tidak perlu diulang.', $halaman);
        $this->assertStringNotContainsString('id="judul-pengumuman"', $halaman);
    }

    /**
     * Dialog tanpa nama hanya diumumkan sebagai "dialog" oleh pembaca layar.
     * Saat judulnya disembunyikan, judul yang sama harus tetap menjadi namanya.
     */
    public function test_a_poster_dialog_still_has_an_accessible_name(): void
    {
        Announcement::factory()->create([
            'title' => 'Waspada Penipuan Rekrutmen',
            'show_text' => false,
            'image' => 'unggahan/pengumuman/poster.png',
            'image_alt' => 'Rekrutmen tidak dipungut biaya',
        ]);

        $this->get('/')->assertOk()
            ->assertSee('aria-label="Waspada Penipuan Rekrutmen"', false);
    }

    /**
     * Pita merek dilewat pada mode poster: selebaran membawa bingkai warnanya
     * sendiri, dan dua pita bertumpuk terbaca sebagai kesalahan cetak.
     */
    public function test_poster_mode_does_not_stack_two_brand_strips(): void
    {
        Announcement::factory()->create([
            'show_text' => false,
            'image' => 'unggahan/pengumuman/poster.png',
            'image_alt' => 'Poster',
        ]);

        $halaman = $this->get('/')->assertOk()->getContent();

        // Pita dialog memakai gradien inline; poster membawa miliknya di dalam
        // gambar, jadi yang dihitung hanya milik dialog.
        $this->assertSame(0, substr_count(
            substr($halaman, strpos($halaman, 'judul-pengumuman') ?: 0),
            'linear-gradient(90deg,#0F2747',
        ));
    }

    public function test_it_is_managed_from_the_panel(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Announcement::factory()->create(['title' => 'Terlihat Di Panel']);

        $this->get(AnnouncementResource::getUrl('index'))->assertOk()->assertSee('Terlihat Di Panel');
        $this->get(AnnouncementResource::getUrl('create'))->assertOk();
    }
}
