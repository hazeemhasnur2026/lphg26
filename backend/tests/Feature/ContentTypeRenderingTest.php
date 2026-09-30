<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\Media;
use App\Models\Post;
use App\Support\VideoEmbed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tipe konten yang punya bentuk tampilan sendiri (CMS-04).
 *
 * Kolomnya sudah ada dan bisa diisi dari panel sejak awal; yang lama tidak ada
 * adalah halaman yang menampilkannya. Bentuk kegagalan itu tidak menimbulkan
 * galat apa pun: redaksi mengunggah PDF laporan, panel menyimpannya, dan
 * pembaca membuka halaman yang isinya hanya pengantar tanpa tombol unduh.
 */
class ContentTypeRenderingTest extends TestCase
{
    use RefreshDatabase;

    private function artikel(ContentType $tipe, array $tambahan = []): Post
    {
        return Post::factory()->create([
            'content_type' => $tipe,
            'category' => 'news',
            'slug' => 'contoh-'.$tipe->value,
        ] + $tambahan);
    }

    public function test_a_report_offers_its_file_for_download(): void
    {
        $post = $this->artikel(ContentType::Report, [
            'document_path' => 'unggahan/dokumen/laporan-2026.pdf',
        ]);

        $this->get("/post/news/{$post->slug}")
            ->assertOk()
            ->assertSee('Unduh Laporan')
            ->assertSee('laporan-2026.pdf');
    }

    public function test_a_video_article_embeds_the_player(): void
    {
        $post = $this->artikel(ContentType::Video, [
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->get("/post/news/{$post->slug}")
            ->assertOk()
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    /**
     * Penyedia yang tidak dikenali tidak boleh menghasilkan iframe kosong.
     *
     * Kotak abu-abu tanpa galat adalah bentuk kerusakan yang paling lama tidak
     * ketahuan. Tautan biasa setidaknya membawa pembaca ke videonya.
     */
    public function test_an_unknown_video_host_falls_back_to_a_plain_link(): void
    {
        $post = $this->artikel(ContentType::Video, [
            'video_url' => 'https://contoh.test/video/123',
        ]);

        $this->get("/post/news/{$post->slug}")
            ->assertOk()
            ->assertSee('Tonton video')
            ->assertDontSee('<iframe', false);
    }

    public function test_a_gallery_shows_its_files_with_the_alt_text_from_the_library(): void
    {
        $post = $this->artikel(ContentType::Gallery);

        $berkas = Media::create([
            'path' => 'pustaka/dermaga.jpg',
            'original_name' => 'dermaga.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'alt_text' => 'Bongkar muat di dermaga Sungai Barito',
            'caption' => 'Dermaga Sungai Barito, Maret 2026',
        ]);
        $post->media()->attach($berkas->id);

        $this->get("/post/news/{$post->slug}")
            ->assertOk()
            // Alt text ditulis sekali di pustaka, bukan diulang per galeri.
            ->assertSee('Bongkar muat di dermaga Sungai Barito')
            ->assertSee('Dermaga Sungai Barito, Maret 2026');
    }

    public function test_an_ordinary_article_gains_none_of_it(): void
    {
        $post = $this->artikel(ContentType::News);

        $this->get("/post/news/{$post->slug}")
            ->assertOk()
            ->assertDontSee('Unduh')
            ->assertDontSee('Tonton video')
            ->assertDontSee('<iframe', false);
    }

    /**
     * Id video dari basis data tidak boleh masuk begitu saja ke atribut src.
     */
    public function test_a_malformed_video_id_is_refused(): void
    {
        $this->assertNull(VideoEmbed::url('https://www.youtube.com/watch?v="><script>x</script>'));
        $this->assertNull(VideoEmbed::url('https://www.youtube.com/watch?v=pendek'));
        $this->assertNull(VideoEmbed::url(null));
    }

    public function test_the_common_youtube_and_vimeo_shapes_all_work(): void
    {
        foreach ([
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
        ] as $tautan) {
            $this->assertSame(
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
                VideoEmbed::url($tautan),
                "Gagal pada bentuk: {$tautan}",
            );
        }

        $this->assertSame('https://player.vimeo.com/video/76979871', VideoEmbed::url('https://vimeo.com/76979871'));
    }
}
