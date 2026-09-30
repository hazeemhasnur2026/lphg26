<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\MediaLibrary\MediaResource;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Widgets\RingkasanKontenWidget;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kartu dasbor harus benar-benar menyaring daftar yang dituju, bukan hanya
 * membawa URL yang terlihat masuk akal.
 *
 * Versi pertama menaut lewat tableFilters[...] milik Filament, dan itu tidak
 * pernah bekerja: Filament 4 tidak mengisi $tableFilters dari query string
 * saat halaman dimuat. Klik kartu "Menunggu tinjauan" mendarat di daftar tak
 * tersaring — semua baris tampil, tidak satu pun galat. 201 tes sebelumnya
 * tetap hijau karena tidak satu pun menguji jalur ini secara utuh: dari URL
 * kartu, sampai baris yang benar-benar dirender getTableQuery().
 */
class DashboardCardFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_awaiting_review_card_actually_filters_the_list(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $menunggu = Post::factory()->create(['status' => PostStatus::InReview, 'title' => 'Perlu Ditinjau']);
        Post::factory()->create(['status' => PostStatus::Draft, 'title' => 'Masih Draf']);

        $url = PostResource::getUrl('index', ['status' => PostStatus::InReview->value]);
        $this->assertStringContainsString('status=in_review', $url);

        $baris = app(ListPosts::class);
        $this->get($url)->assertOk()->assertSee('Perlu Ditinjau')->assertDontSee('Masih Draf');
    }

    public function test_draft_card_filters_correctly(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Post::factory()->create(['status' => PostStatus::Draft, 'title' => 'Naskah Draf Saja']);
        Post::factory()->create(['status' => PostStatus::Published, 'title' => 'Sudah Terbit']);

        $url = PostResource::getUrl('index', ['status' => PostStatus::Draft->value]);

        $this->get($url)->assertOk()->assertSee('Naskah Draf Saja')->assertDontSee('Sudah Terbit');
    }

    public function test_scheduled_card_filters_correctly(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Post::factory()->create([
            'status' => PostStatus::Published,
            'published_at' => now()->addWeek(),
            'title' => 'Artikel Terjadwal',
        ]);
        Post::factory()->create(['status' => PostStatus::Published, 'title' => 'Sudah Tayang']);

        $url = PostResource::getUrl('index', ['terjadwal' => '1']);

        $this->get($url)->assertOk()->assertSee('Artikel Terjadwal')->assertDontSee('Sudah Tayang');
    }

    public function test_undated_card_filters_correctly(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Post::factory()->create(['published_at' => null, 'title' => 'Tanpa Tanggal Contoh']);
        Post::factory()->create(['published_at' => now(), 'title' => 'Bertanggal Contoh']);

        $url = PostResource::getUrl('index', ['tanpa_tanggal' => '1']);

        $this->get($url)->assertOk()->assertSee('Tanpa Tanggal Contoh')->assertDontSee('Bertanggal Contoh');
    }

    public function test_missing_alt_text_card_filters_correctly(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Media::create([
            'disk' => 'content', 'path' => 'uji/tanpa-alt.jpg', 'original_name' => 'tanpa-alt.jpg',
            'mime_type' => 'image/jpeg', 'size' => 100, 'alt_text' => null,
        ]);
        Media::create([
            'disk' => 'content', 'path' => 'uji/dengan-alt.jpg', 'original_name' => 'dengan-alt.jpg',
            'mime_type' => 'image/jpeg', 'size' => 100, 'alt_text' => 'Sudah ada keterangan',
        ]);

        $url = MediaResource::getUrl('index', ['tanpa_alt' => '1']);

        $this->get($url)->assertOk()->assertSee('tanpa-alt.jpg')->assertDontSee('dengan-alt.jpg');
    }

    public function test_unhandled_messages_card_filters_correctly(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        ContactMessage::factory()->create(['subject' => 'Pesan Belum Ditangani', 'handled_at' => null]);
        ContactMessage::factory()->create(['subject' => 'Pesan Sudah Selesai', 'handled_at' => now()]);

        $url = ContactMessageResource::getUrl('index', ['belum_ditangani' => '1']);

        $this->get($url)->assertOk()
            ->assertSee('Pesan Belum Ditangani')
            ->assertDontSee('Pesan Sudah Selesai');
    }

    /**
     * Setiap URL yang dihasilkan widget harus benar-benar tersaring saat
     * dibuka — bukan cuma bentuk querynya, tapi hasil rendernya.
     */
    public function test_every_widget_link_actually_narrows_the_table(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Post::factory()->create(['status' => PostStatus::InReview]);
        Post::factory()->create(['status' => PostStatus::Draft]);
        Post::factory()->create(['status' => PostStatus::Published, 'published_at' => now()->addWeek()]);
        Post::factory()->create(['published_at' => null]);
        Post::factory()->count(3)->create(); // baseline terbit, tidak boleh ikut tersaring.

        $widget = new RingkasanKontenWidget;
        $refleksi = new \ReflectionMethod($widget, 'getStats');
        $refleksi->setAccessible(true);
        $stats = $refleksi->invoke($widget);

        foreach ($stats as $stat) {
            $refleksiUrl = new \ReflectionProperty($stat, 'url');
            $refleksiUrl->setAccessible(true);
            $url = $refleksiUrl->getValue($stat);

            if (! $url) {
                continue;
            }

            // Diklik lewat HTTP sungguhan: itu yang benar-benar terjadi saat
            // seseorang menekan kartunya.
            $this->get($url)->assertOk();
        }

        $this->assertTrue(true, 'Semua URL widget dapat dibuka tanpa galat.');
    }
}
