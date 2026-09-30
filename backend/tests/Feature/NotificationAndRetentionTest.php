<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use App\Support\PostWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pemberitahuan alur redaksi (CMS-03), pendaftaran pustaka (CMS-05), dan
 * pemangkasan riwayat versi (CMS-09).
 */
class NotificationAndRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_a_draft_notifies_the_people_who_can_approve(): void
    {
        $penulis = User::factory()->contributor()->create();
        $peninjau = User::factory()->reviewer()->create();
        $admin = User::factory()->admin()->create();
        $kontributorLain = User::factory()->contributor()->create();

        $post = Post::factory()->draft()->create(['author_id' => $penulis->id]);

        $this->actingAs($penulis);
        PostWorkflow::transition($post, PostStatus::InReview);

        $this->assertCount(1, $peninjau->notifications);
        $this->assertCount(1, $admin->notifications);
        // Kontributor lain tidak punya langkah berikutnya di sini.
        $this->assertCount(0, $kontributorLain->fresh()->notifications);
        // Dan pengajunya sendiri tidak diberi tahu tentang perbuatannya sendiri.
        $this->assertCount(0, $penulis->fresh()->notifications);
    }

    /**
     * Pemberitahuan harus sampai walau antreannya bukan sync.
     *
     * Tes lain di berkas ini lolos tanpa membuktikan apa pun tentang hal ini:
     * phpunit.xml memaksa QUEUE_CONNECTION=sync, sedangkan .env produksi memakai
     * database. Filament\Notifications\DatabaseNotification mengimplementasikan
     * ShouldQueue, jadi pada setelan produksi pemberitahuannya mengendap di
     * tabel jobs menunggu pekerja yang tidak pernah dijalankan aplikasi ini.
     *
     * Tes ini menyetel antreannya ke database dengan sengaja, supaya kekeliruan
     * itu tidak bisa kembali tanpa ketahuan.
     */
    public function test_a_notification_arrives_even_when_the_queue_is_not_sync(): void
    {
        config(['queue.default' => 'database']);

        $penulis = User::factory()->contributor()->create();
        $peninjau = User::factory()->reviewer()->create();
        $post = Post::factory()->draft()->create(['author_id' => $penulis->id]);

        $this->actingAs($penulis);
        PostWorkflow::transition($post, PostStatus::InReview);

        $this->assertSame(1, $peninjau->fresh()->notifications()->count(),
            'Pemberitahuan tidak sampai; kemungkinan mengendap di antrean.');
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_returning_a_draft_tells_the_author_why(): void
    {
        $penulis = User::factory()->contributor()->create();
        $peninjau = User::factory()->reviewer()->create();

        $post = Post::factory()->draft()->create(['author_id' => $penulis->id]);
        $this->actingAs($penulis);
        PostWorkflow::transition($post, PostStatus::InReview);

        $this->actingAs($peninjau);
        PostWorkflow::transition($post, PostStatus::Draft, 'Data produksi belum dicek.');

        $isi = json_encode($penulis->fresh()->notifications->first()->data);

        // Alasan penolakan ikut terbawa. Tanpa itu penulisnya harus menebak,
        // atau bertanya lewat jalur di luar sistem.
        $this->assertStringContainsString('Data produksi belum dicek.', $isi);
    }

    /**
     * Label lonceng harus berbahasa Indonesia, termasuk saat ada yang belum
     * dibaca.
     *
     * Terjemahan Indonesia bawaan Filament punya 'label' tetapi tidak punya
     * 'label_with_unread_count'. Kunci yang tidak diterjemahkan dikembalikan apa
     * adanya, sehingga aria-label tombolnya berbunyi seperti nama kunci. Tidak
     * terlihat mata — yang membacakannya pembaca layar, kepada orang yang paling
     * bergantung padanya.
     */
    public function test_the_notification_bell_label_is_translated(): void
    {
        $this->app->setLocale('id');

        $kunci = 'filament-panels::layout.actions.open_database_notifications.label_with_unread_count';

        $this->assertNotSame($kunci, trans_choice($kunci, 3, ['count' => 3]));
        // Tambalan tidak boleh menghapus kunci lain milik paket.
        $this->assertNotSame(
            'filament-panels::layout.actions.open_database_notifications.label',
            trans('filament-panels::layout.actions.open_database_notifications.label'),
        );
    }

    public function test_archiving_notifies_nobody(): void
    {
        $penulis = User::factory()->contributor()->create();
        $peninjau = User::factory()->reviewer()->create();
        $post = Post::factory()->create(['author_id' => $penulis->id]);

        $this->actingAs($peninjau);
        PostWorkflow::transition($post, PostStatus::Archived);

        // Menarik konten lama adalah keputusan redaksi, bukan giliran orang lain.
        $this->assertCount(0, $penulis->fresh()->notifications);
    }

    public function test_the_media_import_registers_what_is_already_on_disk(): void
    {
        Storage::fake('content');
        Storage::disk('content')->put('images/foto-lama.jpg', 'isi');
        Storage::disk('content')->put('images/catatan.txt', 'bukan media');

        $this->artisan('media:import')->assertSuccessful();

        $this->assertDatabaseHas('media', [
            'path' => 'images/foto-lama.jpg',
            'mime_type' => 'image/jpeg',
            'folder' => 'images',
        ]);
        // Berkas yang bukan media tidak ikut terdaftar.
        $this->assertDatabaseMissing('media', ['path' => 'images/catatan.txt']);

        // Alt text sengaja kosong: menebaknya dari nama berkas lebih buruk
        // daripada tidak ada, karena yang kosong muncul di penyaringnya.
        $this->assertNull(Media::first()->alt_text);
    }

    public function test_running_the_media_import_twice_does_not_duplicate(): void
    {
        Storage::fake('content');
        Storage::disk('content')->put('images/foto.jpg', 'isi');

        $this->artisan('media:import')->assertSuccessful();
        $this->artisan('media:import')->assertSuccessful();

        $this->assertSame(1, Media::count());
    }

    public function test_pruning_keeps_the_newest_versions_and_drops_the_rest(): void
    {
        config(['site.retensi_revisi' => 5]);

        $post = Post::factory()->create();
        for ($i = 1; $i <= 12; $i++) {
            PostRevision::create([
                'post_id' => $post->id, 'version' => $i,
                'snapshot' => ['title' => "versi {$i}"],
            ]);
        }

        $this->artisan('revisions:prune')->assertSuccessful();

        $tersisa = $post->revisions()->orderBy('version')->pluck('version')->all();

        // Lima terakhir, bukan lima pertama: yang dicari orang adalah suntingan
        // terdekat, bukan naskah paling purba.
        $this->assertSame([8, 9, 10, 11, 12], $tersisa);
    }

    public function test_pruning_leaves_articles_under_the_limit_alone(): void
    {
        config(['site.retensi_revisi' => 5]);

        $post = Post::factory()->create();
        for ($i = 1; $i <= 3; $i++) {
            PostRevision::create(['post_id' => $post->id, 'version' => $i, 'snapshot' => []]);
        }

        $this->artisan('revisions:prune')->assertSuccessful();

        $this->assertSame(3, $post->revisions()->count());
    }

    public function test_retention_can_be_switched_off(): void
    {
        config(['site.retensi_revisi' => 0]);

        $post = Post::factory()->create();
        for ($i = 1; $i <= 8; $i++) {
            PostRevision::create(['post_id' => $post->id, 'version' => $i, 'snapshot' => []]);
        }

        $this->artisan('revisions:prune')->assertSuccessful();

        $this->assertSame(8, $post->revisions()->count());
    }
}
