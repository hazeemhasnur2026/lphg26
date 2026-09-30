<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\User;
use App\Support\PostWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alur Draf → Tinjau → Setujui → Terbit → Arsip (CMS-03 pada TOR).
 *
 * Yang diperiksa di sini bukan tombolnya ada, melainkan bahwa alurnya tidak
 * bisa dilompati. Alur persetujuan yang bisa dilewati oleh orang yang paling
 * ingin melewatinya tidak menambah apa pun selain langkah.
 */
class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_contributor_cannot_publish_their_own_draft(): void
    {
        $penulis = User::factory()->contributor()->create();
        $post = Post::factory()->draft()->create(['author_id' => $penulis->id]);

        $this->assertFalse(PostWorkflow::allows($penulis, $post, PostStatus::Published));
        $this->assertFalse(PostWorkflow::allows($penulis, $post, PostStatus::Approved));
        // Yang boleh dilakukannya hanya mengajukan.
        $this->assertTrue(PostWorkflow::allows($penulis, $post, PostStatus::InReview));
    }

    public function test_publishing_cannot_skip_review(): void
    {
        $peninjau = User::factory()->reviewer()->create();
        $post = Post::factory()->draft()->create();

        // Bahkan peninjau tidak bisa melompat dari draf langsung ke terbit.
        // Urutannya melekat pada statusnya, bukan pada haknya.
        $this->assertFalse(PostWorkflow::allows($peninjau, $post, PostStatus::Published));
        $this->assertTrue(PostWorkflow::allows($peninjau, $post, PostStatus::InReview));
    }

    public function test_the_full_route_from_draft_to_published(): void
    {
        $penulis = User::factory()->contributor()->create();
        $peninjau = User::factory()->reviewer()->create();
        $post = Post::factory()->draft()->create([
            'author_id' => $penulis->id,
            'published_at' => null,
        ]);

        $this->actingAs($penulis);
        PostWorkflow::transition($post, PostStatus::InReview);
        $this->assertSame(PostStatus::InReview, $post->fresh()->status);
        $this->assertSame($penulis->id, $post->fresh()->submitted_by);

        $this->actingAs($peninjau);
        PostWorkflow::transition($post, PostStatus::Approved, 'Sudah rapi.');
        $this->assertSame($peninjau->id, $post->fresh()->reviewed_by);
        $this->assertSame('Sudah rapi.', $post->fresh()->review_note);

        PostWorkflow::transition($post, PostStatus::Published);
        $post->refresh();

        $this->assertSame(PostStatus::Published, $post->status);
        // Tanggal terbit terisi sendiri saat tidak dijadwalkan.
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->isVisibleToPublic());
    }

    public function test_publishing_does_not_overwrite_a_schedule_someone_set(): void
    {
        $peninjau = User::factory()->reviewer()->create();
        $besok = now()->addDay()->startOfMinute();

        $post = Post::factory()->create([
            'status' => PostStatus::Approved,
            'published_at' => $besok,
        ]);

        $this->actingAs($peninjau);
        PostWorkflow::transition($post, PostStatus::Published);

        $this->assertTrue($post->fresh()->published_at->equalTo($besok));
        // Statusnya terbit, tapi jadwalnya belum tiba: belum tampil.
        $this->assertFalse($post->fresh()->isVisibleToPublic());
    }

    public function test_a_contributor_loses_the_pen_once_it_is_submitted(): void
    {
        $penulis = User::factory()->contributor()->create();
        $post = Post::factory()->awaitingReview()->create(['author_id' => $penulis->id]);

        // Naskah yang sedang dibaca peninjau tidak boleh berubah di bawah
        // tangannya.
        $this->assertFalse(PostWorkflow::canEdit($penulis, $post));
    }

    public function test_a_contributor_cannot_touch_someone_elses_draft(): void
    {
        $penulis = User::factory()->contributor()->create();
        $punyaOrangLain = Post::factory()->draft()->create([
            'author_id' => User::factory()->contributor()->create()->id,
        ]);

        $this->assertFalse(PostWorkflow::canEdit($penulis, $punyaOrangLain));
    }

    public function test_every_status_change_leaves_a_trace(): void
    {
        $peninjau = User::factory()->reviewer()->create();
        $post = Post::factory()->draft()->create();

        $this->actingAs($peninjau);
        PostWorkflow::transition($post, PostStatus::InReview);
        PostWorkflow::transition($post, PostStatus::Approved);

        $jejak = AuditLog::where('action', 'post.status')->get();

        $this->assertCount(2, $jejak);
        // Nama pelaku ikut tersalin, supaya jejaknya tetap terbaca setelah
        // akunnya dihapus.
        $this->assertSame($peninjau->name, $jejak->first()->user_name);
    }
}
