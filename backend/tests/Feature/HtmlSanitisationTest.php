<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\SafeHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Badan artikel dicetak ke halaman dengan {!! !!}, jadi apa pun yang tersimpan
 * di sana berjalan di browser pengunjung.
 *
 * Yang bisa mengisinya termasuk Kontributor, peran paling rendah di panel.
 * Alur persetujuan tidak menolong: peninjau membaca tulisan, bukan markup, dan
 * sebaris skrip di antara paragraf tidak terlihat olehnya.
 */
class HtmlSanitisationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function muatanBerbahaya(): array
    {
        return [
            'script polos' => ['<script>alert(1)</script>'],
            'script dengan spasi' => ['<script >alert(1)</script >'],
            'besar kecil campur' => ['<ScRiPt>alert(1)</sCrIpT>'],
            'img onerror' => ['<img src=x onerror=alert(1)>'],
            'svg onload' => ['<svg onload=alert(1)></svg>'],
            'onclick pada paragraf' => ['<p onclick="alert(1)">teks</p>'],
            'href javascript' => ['<a href="javascript:alert(1)">klik</a>'],
            'href data' => ['<a href="data:text/html,<script>x</script>">klik</a>'],
            'img data svg' => ['<img src="data:image/svg+xml,<svg onload=alert(1)>">'],
            'iframe' => ['<iframe src="//jahat.test"></iframe>'],
            'object' => ['<object data="jahat.swf"></object>'],
            'formulir phishing' => ['<form action="//jahat.test"><input name="sandi"></form>'],
            'meta refresh' => ['<meta http-equiv="refresh" content="0;url=//jahat.test">'],
            'base href' => ['<base href="//jahat.test/">'],
        ];
    }

    #[DataProvider('muatanBerbahaya')]
    public function test_dangerous_markup_never_reaches_the_page(string $muatan): void
    {
        $bersih = SafeHtml::clean($muatan);

        $this->assertDoesNotMatchRegularExpression(
            '/<script|<iframe|<object|<form|<input|<svg|<meta|<base|on[a-z]+\s*=|javascript:/i',
            $bersih,
            "Muatan lolos penyaring: {$bersih}",
        );
    }

    /**
     * CSS sanggup melakukan lebih daripada mengatur huruf: satu div dengan
     * position:fixed selebar layar dapat menutupi seluruh halaman, dan itu
     * cukup untuk memasang tampilan palsu tanpa sebaris JavaScript pun.
     */
    public function test_css_cannot_be_used_to_cover_the_page(): void
    {
        $bersih = SafeHtml::clean('<div style="position:fixed;top:0;left:0;z-index:9999">x</div>');

        $this->assertDoesNotMatchRegularExpression('/position|z-index|top|left/i', $bersih);
    }

    public function test_css_cannot_reach_outside_itself(): void
    {
        $this->assertStringNotContainsString('url(', SafeHtml::clean('<p style="background:url(//jahat.test/lacak)">x</p>'));
        $this->assertStringNotContainsString('expression', SafeHtml::clean('<p style="width:expression(alert(1))">x</p>'));
    }

    /**
     * Penyaring yang membuang terlalu banyak sama merugikannya dengan yang
     * membuang terlalu sedikit — hanya kerusakannya lebih mudah terlihat.
     */
    public function test_ordinary_article_markup_survives(): void
    {
        $asli = '<p style="text-align: justify"><strong>Tebal</strong> dan <em>miring</em>, '
            .'<a href="/page/publication/news">tautan</a>, '
            .'<img src="images/foto.jpg" alt="Keterangan"></p><ul><li>daftar</li></ul>';

        $bersih = SafeHtml::clean($asli);

        foreach (['<strong>', '<em>', '<ul>', '<li>', 'text-align: justify',
            'href="/page/publication/news"', 'src="images/foto.jpg"', 'alt="Keterangan"'] as $harusAda) {
            $this->assertStringContainsString($harusAda, $bersih, "Hilang: {$harusAda}");
        }
    }

    /**
     * Gambar artikel hasil crawl ditulis relatif. Tanpa allowRelativeMedias(),
     * Symfony membuang setiap URL tanpa skema dan seluruh gambar lenyap dari 35
     * artikel sekaligus — halamannya tetap tampil, hanya tanpa gambar.
     */
    public function test_relative_urls_are_kept(): void
    {
        $this->assertStringContainsString('src="images/foto.jpg"', SafeHtml::clean('<img src="images/foto.jpg">'));
        $this->assertStringContainsString('href="/page/berita"', SafeHtml::clean('<a href="/page/berita">k</a>'));
    }

    public function test_outbound_links_cannot_control_the_tab_they_came_from(): void
    {
        $this->assertStringContainsString('rel="noopener noreferrer"',
            SafeHtml::clean('<a href="https://contoh.test" target="_blank">k</a>'));
    }

    /** Ujung ke ujung: lewat model dan halaman sungguhan, bukan hanya kelasnya. */
    public function test_a_script_stored_in_an_article_does_not_reach_the_visitor(): void
    {
        $post = Post::factory()->create([
            'category' => 'news',
            'slug' => 'artikel-dengan-skrip',
            'body' => '<p>Isi biasa.</p><script>window.dicuri=1</script><img src=x onerror="window.dicuri2=1">',
        ]);

        $halaman = $this->get("/post/news/{$post->slug}")->assertOk()->getContent();

        $this->assertStringContainsString('Isi biasa.', $halaman);
        $this->assertStringNotContainsString('window.dicuri', $halaman);
        $this->assertStringNotContainsString('onerror', $halaman);
    }
}
