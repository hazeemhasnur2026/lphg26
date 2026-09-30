<?php

namespace Tests\Feature;

use App\Filament\Analitik\GrafikPeristiwaWidget;
use App\Filament\Analitik\PeringkatWidget;
use App\Filament\Analitik\RingkasanPeristiwaWidget;
use App\Filament\Pages\Analitik;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;
use Tests\TestCase;

/**
 * Analitik (CMS-10).
 *
 * Yang diuji di sini bukan "apakah angkanya benar" — itu pertanyaan tentang
 * penjumlahan, dan penjumlahan tidak pernah salah. Yang diuji adalah tiga hal
 * yang bisa salah tanpa terlihat: peristiwa yang tidak tercatat, peristiwa
 * karangan yang ikut tercatat, dan data yang tersimpan lebih banyak daripada
 * yang dijanjikan kebijakan privasi.
 */
class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string, mixed> $muatan */
    private function kirim(array $muatan): TestResponse
    {
        return $this->postJson('/analitik/peristiwa', $muatan);
    }

    public function test_the_four_events_the_tor_asks_for_are_recorded(): void
    {
        $peristiwa = [
            'cta_click' => 'kirim-pesan',
            'file_download' => 'laporan-tahunan-2025.pdf',
            'search' => 'lowongan kerja',
            'content_read' => 'Hasnur Group meresmikan jetty baru',
        ];

        foreach ($peristiwa as $nama => $label) {
            $this->kirim(['nama' => $nama, 'label' => $label, 'jalur' => '/', 'bahasa' => 'id'])
                ->assertNoContent();
        }

        $this->assertSame(4, AnalyticsEvent::count());

        foreach ($peristiwa as $nama => $label) {
            $this->assertDatabaseHas('analytics_events', ['name' => $nama, 'label' => $label]);
        }
    }

    public function test_an_invented_event_name_is_refused(): void
    {
        // Alamat endpoint-nya terlihat oleh siapa pun yang membuka sumber
        // halaman. Tanpa penyaring, nama peristiwa karangan masuk ke papan
        // analitik dan tidak ada cara membedakannya dari yang asli sesudahnya.
        $this->kirim(['nama' => 'promo_kompetitor', 'label' => 'apa saja'])
            ->assertStatus(422);

        $this->assertSame(0, AnalyticsEvent::count());
    }

    public function test_only_the_path_of_a_full_url_is_stored(): void
    {
        $this->kirim([
            'nama' => 'search',
            'label' => 'csr',
            'jalur' => 'https://situs-lain.example/cari?q=csr#hasil',
        ])->assertNoContent();

        // Host dibuang, kueri disimpan, fragmen tidak ikut: seluruh baris di
        // tabel ini berasal dari situs ini sendiri, dan URL berdomain lain di
        // kolom itu hanya menyesatkan yang membacanya nanti.
        $this->assertSame('/cari?q=csr', AnalyticsEvent::first()->path);
    }

    public function test_a_label_longer_than_the_column_is_trimmed_not_refused(): void
    {
        // Judul artikel yang kebetulan panjang adalah judul yang sah. Membalas
        // 422 kepadanya berarti kehilangan peristiwanya sama sekali demi
        // beberapa karakter yang tidak dibaca siapa pun.
        $this->kirim(['nama' => 'content_read', 'label' => str_repeat('a', 400)])
            ->assertNoContent();

        $this->assertLessThanOrEqual(160, mb_strlen(AnalyticsEvent::first()->label));
    }

    public function test_the_visitor_fingerprint_hides_the_address_it_was_made_from(): void
    {
        $this->kirim(['nama' => 'cta_click', 'label' => 'kirim-pesan'])->assertNoContent();

        $peristiwa = AnalyticsEvent::first();

        $this->assertNotNull($peristiwa->visitor);
        // Kebijakan privasi menjanjikan alamat IP tidak disimpan. Sidiknya hasil
        // HMAC, jadi alamatnya tidak boleh muncul di dalamnya dalam bentuk apa
        // pun — termasuk sebagai bagian dari teksnya.
        $this->assertStringNotContainsString('127.0.0.1', $peristiwa->visitor);
        $this->assertSame(32, strlen($peristiwa->visitor));
    }

    public function test_the_same_visitor_gets_the_same_fingerprint_a_different_one_does_not(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])
            ->kirim(['nama' => 'search', 'label' => 'a']);
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])
            ->kirim(['nama' => 'search', 'label' => 'b']);
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->kirim(['nama' => 'search', 'label' => 'c']);

        // Dua peristiwa dari satu orang harus terhitung sebagai satu
        // pengunjung, kalau tidak angka "pengunjung" di papan tidak berbeda
        // dari angka "peristiwa" dan tidak menjawab apa pun tambahan.
        $this->assertSame(2, AnalyticsEvent::distinct()->count('visitor'));
    }

    public function test_recording_can_be_switched_off_without_breaking_the_page(): void
    {
        config(['services.analytics.internal' => false]);

        // 204, bukan 404: yang bertanya adalah skrip di halaman, dan galat di
        // konsol pengunjung tidak memperbaiki apa pun.
        $this->kirim(['nama' => 'cta_click', 'label' => 'kirim-pesan'])->assertNoContent();

        $this->assertSame(0, AnalyticsEvent::count());
    }

    public function test_the_tracking_script_runs_without_a_third_party_platform(): void
    {
        config([
            'services.analytics.ga_id' => null,
            'services.analytics.gtm_id' => null,
            'services.analytics.internal' => true,
        ]);

        $halaman = $this->get('/')->assertOk();

        // Inilah yang membuat keempat peristiwa TOR punya angka hari ini:
        // skripnya jalan walaupun platform yang disetujui belum dipilih.
        //
        // Dibandingkan dengan keluaran Js::from, bukan dengan alamatnya apa
        // adanya: itu bentuk yang benar-benar tertulis di halaman, garis
        // miringnya ikut diloloskan.
        $halaman->assertSee(Js::from(route('analytics.track'))->toHtml(), false);
        $halaman->assertDontSee('googletagmanager.com', false);
    }

    /**
     * Token CSRF beacon diambil dari cookie, bukan dari HTML.
     *
     * Ini pemeriksaan atas isi halaman, bukan atas perilaku CSRF-nya: middleware
     * CSRF melewatkan dirinya sendiri saat tes berjalan, jadi tidak ada cara
     * memancing 419 dari sini. Yang ditahan pemeriksaan ini adalah kemundurannya
     * — kembali memakai satu token yang dibekukan saat halaman dirender.
     *
     * Bug aslinya: token di HTML beku sejak halaman dirender, sedangkan cookie
     * XSRF-TOKEN disegarkan tiap respons. Begitu sesi berganti — umurnya lewat,
     * atau APP_KEY berubah di mesin pengembang — token beku itu tidak cocok lagi
     * dan *setiap* peristiwa dari tab tersebut dijawab 419 tanpa pernah
     * tersimpan. Dibuktikan dengan permintaan HTTP sungguhan: token beku 419,
     * token dari cookie 204.
     */
    public function test_the_beacon_takes_its_csrf_token_from_the_cookie(): void
    {
        config([
            'services.analytics.ga_id' => null,
            'services.analytics.gtm_id' => null,
            'services.analytics.internal' => true,
        ]);

        $halaman = $this->get('/')->assertOk();

        $halaman->assertSee('XSRF-TOKEN=', false);
        $halaman->assertSee('X-XSRF-TOKEN', false);
        // Peristiwa yang ditolak harus meninggalkan jejak. Jawaban 419 bukan
        // penolakan promise, dan diamnya itulah yang membuat bug pertama lolos.
        $halaman->assertSee('console.warn', false);
    }

    public function test_the_third_party_loader_stays_off_until_an_id_is_filled(): void
    {
        config([
            'services.analytics.ga_id' => null,
            'services.analytics.gtm_id' => null,
            'services.analytics.internal' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('googletagmanager.com', false);
    }

    public function test_events_past_their_retention_are_pruned(): void
    {
        config(['site.retensi_analitik_bulan' => 12]);

        AnalyticsEvent::insert([
            ['name' => 'search', 'label' => 'lama', 'created_at' => now()->subMonths(13)],
            ['name' => 'search', 'label' => 'baru', 'created_at' => now()->subMonths(11)],
        ]);

        $this->artisan('model:prune', ['--model' => [AnalyticsEvent::class]])->assertSuccessful();

        $this->assertDatabaseMissing('analytics_events', ['label' => 'lama']);
        $this->assertDatabaseHas('analytics_events', ['label' => 'baru']);
    }

    public function test_retention_set_to_zero_prunes_nothing(): void
    {
        // Salah ketik pada satu setelan tidak boleh berujung pada terhapusnya
        // seluruh riwayat.
        config(['site.retensi_analitik_bulan' => 0]);

        AnalyticsEvent::insert([
            ['name' => 'search', 'label' => 'sangat lama', 'created_at' => now()->subYears(5)],
        ]);

        $this->artisan('model:prune', ['--model' => [AnalyticsEvent::class]])->assertSuccessful();

        $this->assertDatabaseHas('analytics_events', ['label' => 'sangat lama']);
    }

    public function test_the_dashboard_is_readable_by_reviewers_and_hidden_from_contributors(): void
    {
        $this->actingAs(User::factory()->reviewer()->create());
        $this->assertTrue(Analitik::canAccess());
        $this->get(Analitik::getUrl())->assertOk();

        // Papan ini memuat kata kunci yang diketik pengunjung; pekerjaan
        // kontributor adalah menulis naskahnya sendiri.
        $this->actingAs(User::factory()->contributor()->create());
        $this->assertFalse(Analitik::canAccess());
        $this->get(Analitik::getUrl())->assertForbidden();
    }

    /**
     * Widget diuji lewat Livewire, bukan lewat HTML halamannya.
     *
     * Widget Filament dimuat belakangan: permintaan pertama hanya mengirim
     * kerangkanya, dan isinya menyusul lewat Livewire. Memeriksa keluaran GET
     * halaman berarti memeriksa kerangka kosong, dan tes seperti itu tetap
     * hijau walaupun seluruh kueri di dalam widgetnya salah.
     */
    /**
     * Tiap widget halaman Analitik harus bisa dipetakan balik dari namanya ke
     * kelasnya oleh Livewire.
     *
     * Ini invarian yang pernah pecah, dan cara pecahnya pantas dicatat: seluruh
     * tes lain di berkas ini tetap hijau sementara halamannya tidak bisa dipakai
     * sama sekali di peramban.
     *
     * Livewire sanggup menurunkan nama komponen dari nama kelas saat merender,
     * jadi pemuatan pertama menghasilkan HTML yang utuh. Permintaan berikutnya —
     * dan seluruh isi halaman ini datang lewat permintaan berikutnya, karena
     * widgetnya dimuat belakangan — mengirim nama itu kembali dan Livewire harus
     * memetakannya ke kelas. Pemetaan balik hanya bekerja untuk kelas yang
     * terdaftar. Yang tidak terdaftar melempar ComponentNotFoundException, dan
     * ReleaseToken::verify mengubahnya menjadi "419 Page Expired" — pesan yang
     * tidak menyebut widget mana pun, sehingga tidak menuntun ke sebabnya.
     *
     * Widget ini sengaja berada di luar app/Filament/Widgets supaya tidak ikut
     * ditemukan discoverWidgets() dan tidak muncul di Dashboard. Harganya adalah
     * pendaftaran yang harus ditulis sendiri di AdminPanelProvider, dan
     * pemeriksaan inilah yang menahannya supaya tidak terlupa lagi.
     */
    public function test_every_widget_on_the_analytics_page_is_registered_with_livewire(): void
    {
        $halaman = new Analitik;
        $metode = new \ReflectionMethod($halaman, 'getFooterWidgets');
        $registry = app(ComponentRegistry::class);

        $widgets = $metode->invoke($halaman);

        $this->assertNotEmpty($widgets);

        foreach ($widgets as $widget) {
            $kelas = $widget instanceof WidgetConfiguration ? $widget->widget : $widget;
            $nama = $registry->getName($kelas);

            try {
                $balik = $registry->getClass($nama);
            } catch (ComponentNotFoundException) {
                $this->fail(
                    "Widget {$kelas} tidak terdaftar di Livewire. Nama '{$nama}' tidak bisa "
                    .'dipetakan balik ke kelasnya, dan halaman Analitik akan menjawab 419 '
                    .'Page Expired begitu widgetnya dimuat. Tambahkan kelasnya ke '
                    .'->livewireComponents([...]) di AdminPanelProvider.'
                );
            }

            $this->assertSame($kelas, $balik);
        }
    }

    public function test_the_ranking_widget_counts_events_and_visitors(): void
    {
        AnalyticsEvent::insert([
            ['name' => 'cta_click', 'label' => 'kirim-pesan', 'visitor' => 'a', 'created_at' => now()->subDay()],
            ['name' => 'cta_click', 'label' => 'kirim-pesan', 'visitor' => 'a', 'created_at' => now()],
            ['name' => 'cta_click', 'label' => 'kirim-pesan', 'visitor' => 'b', 'created_at' => now()],
            ['name' => 'cta_click', 'label' => 'kirim-lamaran', 'visitor' => 'b', 'created_at' => now()],
            ['name' => 'search', 'label' => 'lowongan kerja', 'visitor' => 'a', 'created_at' => now()],
        ]);

        $this->actingAs(User::factory()->admin()->create());

        $peringkat = Livewire::test(PeringkatWidget::class, ['peristiwa' => 'cta_click'])
            ->assertSee('kirim-pesan')
            ->assertSee('kirim-lamaran')
            // Peristiwa jenis lain tidak ikut masuk ke peringkat CTA.
            ->assertDontSee('lowongan kerja');

        $baris = $peringkat->instance()->getTable()->getRecords();

        // Tiga klik dari dua orang. Dua angka yang berbeda, dan hanya yang
        // kedua berarti sesuatu tentang ketertarikan.
        $this->assertSame(3, (int) $baris->first()->jumlah);
        $this->assertSame(2, (int) $baris->first()->pengunjung);
    }

    public function test_the_summary_and_chart_widgets_render(): void
    {
        AnalyticsEvent::insert([
            ['name' => 'content_read', 'label' => 'Sebuah artikel', 'visitor' => 'a', 'created_at' => now()],
        ]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(RingkasanPeristiwaWidget::class)
            ->assertOk()
            ->assertSee('Artikel dibaca tuntas');

        Livewire::test(GrafikPeristiwaWidget::class)
            ->assertOk()
            // Rentang bawaan tiga puluh hari, dan grafiknya ikut berganti saat
            // rentangnya diganti tanpa halaman dimuat ulang.
            ->assertSet('filter', '30')
            ->set('filter', '7')
            ->assertOk();
    }

    public function test_sample_events_are_marked_and_can_be_removed_without_touching_real_ones(): void
    {
        AnalyticsEvent::insert([
            ['name' => 'search', 'label' => 'sungguhan', 'created_at' => now()],
        ]);

        $this->artisan('analitik:contoh', ['--hari' => 3])->assertSuccessful();

        $contoh = AnalyticsEvent::contoh()->count();
        $this->assertGreaterThan(0, $contoh);

        // Dijalankan dua kali tanpa penolakan, papannya memperlihatkan angka
        // dua kali lebih besar tanpa satu pun peristiwa baru yang terjadi.
        $this->artisan('analitik:contoh', ['--hari' => 3])->assertFailed();
        $this->assertSame($contoh, AnalyticsEvent::contoh()->count());

        $this->artisan('analitik:contoh', ['--hapus' => true])->assertSuccessful();

        $this->assertSame(0, AnalyticsEvent::contoh()->count());
        $this->assertDatabaseHas('analytics_events', ['label' => 'sungguhan']);
    }
}
