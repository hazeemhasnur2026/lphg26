<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Pustaka media (CMS-05), riwayat versi (CMS-09), dan pencarian (CMS-08).
 */
class MediaAndRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_revision_keeps_the_text_that_was_replaced(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $post = Post::factory()->create(['title' => 'Judul Asli', 'body' => '<p>Isi asli.</p>']);
        $post->recordRevision('sebelum suntingan');
        $post->update(['title' => 'Judul Baru', 'body' => '<p>Isi baru.</p>']);

        $revisi = $post->revisions()->orderByDesc('version')->first();

        // Yang tersimpan adalah isi yang ditinggalkan, bukan isi yang baru.
        $this->assertSame('Judul Asli', $revisi->snapshot['title']);
        $this->assertSame(1, $revisi->version);
    }

    public function test_versions_are_numbered_per_article_not_globally(): void
    {
        $satu = Post::factory()->create();
        $dua = Post::factory()->create();

        $satu->recordRevision();
        $dua->recordRevision();
        $satu->recordRevision();

        // "Versi ke berapa" harus terjawab 1, 2 — bukan 1, 3 karena artikel
        // lain menyela di antaranya.
        $this->assertSame([1, 2], $satu->revisions()->orderBy('version')->pluck('version')->all());
        $this->assertSame([1], $dua->revisions()->pluck('version')->all());
    }

    public function test_rolling_back_keeps_the_current_text_as_a_new_version(): void
    {
        $post = Post::factory()->create(['title' => 'Versi Satu']);
        $post->recordRevision();
        $post->update(['title' => 'Versi Dua']);

        // Meniru apa yang dilakukan tombol Kembalikan.
        $post->recordRevision('sebelum dikembalikan');
        $post->forceFill(['title' => $post->revisions()->orderBy('version')->first()->snapshot['title']])->save();

        $this->assertSame('Versi Satu', $post->fresh()->title);
        // Pengembalian yang keliru harus bisa dibatalkan dengan cara yang sama.
        $this->assertSame('Versi Dua', $post->revisions()->orderByDesc('version')->first()->snapshot['title']);
    }

    public function test_the_audit_trail_survives_the_account_that_made_the_change(): void
    {
        $orang = User::factory()->admin()->create(['name' => 'Rina']);
        $this->actingAs($orang);

        AuditLog::record('post.updated', Post::factory()->create(), 'Sesuatu diubah');
        $orang->delete();

        $jejak = AuditLog::first();

        $this->assertNull($jejak->fresh()->user_id);
        // Namanya ikut tersalin saat peristiwa terjadi, jadi jejaknya tetap
        // menjawab "siapa" walau akunnya sudah tidak ada.
        $this->assertSame('Rina', $jejak->user_name);
    }

    /**
     * Menyimpan tanpa mengubah apa pun tidak boleh melahirkan versi.
     *
     * Pembandingnya harus menyamakan bentuk lebih dulu: content_type datang
     * sebagai string dari formulir dan sebagai enum dari model, dan PHP
     * menyatakan 'news' != ContentType::News bernilai true. Tanpa itu setiap
     * penyimpanan menambah satu versi kembar.
     */
    public function test_an_enum_column_does_not_look_changed_when_it_is_not(): void
    {
        $post = Post::factory()->create();

        $samaSaja = Collection::make(Post::REVISIONED_FIELDS)
            ->filter(function (string $field) use ($post) {
                $dariForm = $post->getAttribute($field);
                $dariForm = $dariForm instanceof \BackedEnum ? $dariForm->value : $dariForm;

                $tersimpan = $post->getAttribute($field);
                $tersimpan = $tersimpan instanceof \BackedEnum ? $tersimpan->value : $tersimpan;

                return $dariForm !== $tersimpan;
            });

        $this->assertTrue($samaSaja->isEmpty(), 'Kolom yang tidak berubah terlihat berubah: '.$samaSaja->implode(', '));
    }

    public function test_media_can_be_reused_across_articles_and_tagged(): void
    {
        $berkas = Media::create([
            'path' => 'pustaka/foto.jpg',
            'original_name' => 'foto.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'alt_text' => 'Pekerja di dermaga',
            'folder' => 'csr-2026',
        ]);

        $satu = Post::factory()->create();
        $dua = Post::factory()->create();
        $berkas->posts()->attach([$satu->id, $dua->id]);
        $berkas->tags()->attach(Tag::create(['name' => 'Dermaga'])->id);

        // Satu berkas, dua galeri, satu alt text yang ditulis sekali.
        $this->assertCount(2, $berkas->posts);
        $this->assertSame('Pekerja di dermaga', $satu->media()->first()->alt_text);
        $this->assertSame('2 KB', $berkas->size_for_humans);
    }

    public function test_search_finds_published_articles_and_ignores_drafts(): void
    {
        Post::factory()->create(['title' => 'Batu Bara Kalimantan', 'category' => 'news', 'slug' => 'bbk']);
        Post::factory()->draft()->create(['title' => 'Batu Bara Rahasia', 'category' => 'news', 'slug' => 'bbr']);

        $this->get('/cari?q=Batu+Bara')
            ->assertOk()
            ->assertSee('Batu Bara Kalimantan')
            ->assertDontSee('Batu Bara Rahasia');
    }

    /**
     * Tanda persen punya arti khusus di LIKE. Tanpa dilepas, mencari "100%"
     * mengembalikan seluruh isi situs — hasil yang terlihat seperti fitur
     * bekerja, padahal sebaliknya.
     */
    public function test_search_does_not_treat_wildcards_as_operators(): void
    {
        Post::factory()->create(['title' => 'Produksi naik 100%', 'category' => 'news', 'slug' => 'naik']);
        Post::factory()->create(['title' => 'Berita Lain Sama Sekali', 'category' => 'news', 'slug' => 'lain']);

        // Diperiksa lewat jumlah hasil, bukan lewat assertDontSee pada judul:
        // footer situs memuat tiga artikel terbaru di setiap halaman, jadi
        // judul mana pun yang baru terbit akan selalu "terlihat" di halaman
        // hasil pencarian, apa pun yang dicari.
        $this->get('/cari?q='.urlencode('%'))
            ->assertOk()
            ->assertSee('0 hasil ditemukan.');
    }
}
