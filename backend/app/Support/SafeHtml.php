<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Menyaring HTML yang datang dari luar sebelum dicetak ke halaman.
 *
 * Badan artikel memang berisi HTML, dan itu disengaja: 35 artikel hasil crawl
 * situs lama membawa markup aslinya, dan editor kaya di panel menghasilkan HTML.
 * Karena itu view mencetaknya dengan {!! !!}, bukan {{ }}.
 *
 * Yang hilang selama ini adalah langkah di antaranya. Tanpa penyaring, siapa
 * pun yang boleh mengisi badan artikel — termasuk Kontributor, peran paling
 * rendah — dapat menyisipkan <script> yang kemudian berjalan di browser setiap
 * pengunjung. Alur persetujuan tidak menolongnya: peninjau membaca tulisan,
 * bukan markup, dan sebaris skrip di antara paragraf tidak terlihat.
 *
 * Daftar yang diizinkan disusun dari isi yang benar-benar ada, bukan dari
 * daftar umum. Dua belas tag yang muncul di seluruh artikel hasil migrasi
 * ditambah beberapa yang bisa dihasilkan editor kaya. Sisanya dibuang.
 */
class SafeHtml
{
    /**
     * Properti CSS yang boleh bertahan di atribut style.
     *
     * Style tidak dibiarkan lolos apa adanya walaupun isi yang ada sekarang
     * jinak seluruhnya — hanya empat nilai berbeda, semuanya tipografi. CSS
     * sanggup melakukan lebih daripada mengatur huruf: satu div dengan
     * position:fixed selebar layar dapat menutupi seluruh halaman, dan itu
     * cukup untuk memasang tampilan palsu tanpa sebaris JavaScript pun.
     */
    private const CSS_DIIZINKAN = [
        'text-align', 'font-weight', 'font-style', 'text-decoration',
        'margin', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom',
        'padding-left', 'display', 'width', 'height', 'max-width',
    ];

    private static ?HtmlSanitizer $penyaring = null;

    /** Membersihkan HTML dari sumber yang tidak dipercaya. */
    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $bersih = static::penyaring()->sanitize($html);

        return static::saringStyle($bersih);
    }

    private static function penyaring(): HtmlSanitizer
    {
        return static::$penyaring ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                // Dasar aman bawaan Symfony, lalu dipersempit dan dilebarkan
                // seperlunya. Mulai dari nol berarti menemukan ulang daftar
                // yang sudah dipikirkan orang lain lebih lama.
                ->allowSafeElements()

                // Dua belas tag yang benar-benar muncul di artikel hasil crawl.
                ->allowElement('p', ['class', 'style'])
                ->allowElement('span', ['class', 'style'])
                ->allowElement('div', ['class', 'style'])
                ->allowElement('em', ['class'])
                ->allowElement('strong', ['class'])
                ->allowElement('br')
                ->allowElement('hr')
                ->allowElement('ul', ['class'])
                ->allowElement('ol', ['class'])
                ->allowElement('li', ['class'])
                ->allowElement('a', ['href', 'title', 'class'])
                ->allowElement('img', ['src', 'alt', 'width', 'height', 'class', 'style'])

                // Yang bisa dihasilkan editor kaya di panel.
                ->allowElement('h2', ['class'])
                ->allowElement('h3', ['class'])
                ->allowElement('h4', ['class'])
                ->allowElement('blockquote', ['class'])
                ->allowElement('code')
                ->allowElement('pre')
                ->allowElement('b')
                ->allowElement('i')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('table', ['class'])
                ->allowElement('thead')
                ->allowElement('tbody')
                ->allowElement('tr')
                ->allowElement('th', ['colspan', 'rowspan'])
                ->allowElement('td', ['colspan', 'rowspan'])

                // Skema tautan dan media dibatasi. Tanpa ini, href="javascript:…"
                // tetap lolos karena <a href> memang diizinkan.
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->allowMediaSchemes(['http', 'https'])

                // Wajib, dan mudah terlewat: tanpa keduanya Symfony membuang
                // setiap URL yang tidak berskema. Gambar di artikel hasil crawl
                // ditulis relatif — src="images/foo.jpg" — sehingga seluruh
                // gambar akan lenyap dari 35 artikel sekaligus, dan halamannya
                // tetap tampil normal selain kehilangan gambarnya.
                ->allowRelativeLinks()
                ->allowRelativeMedias()

                // Tautan keluar tidak boleh memberi halaman tujuan kendali atas
                // tab asalnya lewat window.opener.
                ->forceAttribute('a', 'rel', 'noopener noreferrer')

                // Dibuang berikut isinya, bukan sekadar tagnya: teks di dalam
                // <script> bukan tulisan yang ingin dibaca siapa pun.
                ->dropElement('script')
                ->dropElement('style')
                ->dropElement('iframe')
                ->dropElement('object')
                ->dropElement('embed')
                ->dropElement('form')
                ->dropElement('input')
                ->dropElement('button')
                ->dropElement('textarea')
                ->dropElement('select')
                ->dropElement('link')
                ->dropElement('meta')
                ->dropElement('base')

                // Batas ukuran, supaya satu artikel raksasa tidak menghabiskan
                // memori proses saat disaring.
                ->withMaxInputLength(2_000_000)
        );
    }

    /**
     * Menyisakan hanya properti CSS yang ada di daftar izin.
     *
     * Symfony membiarkan isi atribut style apa adanya; ia menyaring tag dan
     * atribut, bukan CSS. Jadi penyaringan propertinya dikerjakan di sini.
     */
    private static function saringStyle(string $html): string
    {
        return preg_replace_callback(
            '/\sstyle="([^"]*)"/i',
            function (array $m): string {
                $aman = [];

                foreach (explode(';', $m[1]) as $bagian) {
                    if (! str_contains($bagian, ':')) {
                        continue;
                    }

                    [$properti, $nilai] = explode(':', $bagian, 2);
                    $properti = strtolower(trim($properti));
                    $nilai = trim($nilai);

                    // url() dan expression() dibuang berapa pun propertinya:
                    // keduanya jalan keluar dari CSS menuju hal lain.
                    if (! in_array($properti, self::CSS_DIIZINKAN, true)
                        || preg_match('/url\s*\(|expression\s*\(|javascript:/i', $nilai) === 1) {
                        continue;
                    }

                    $aman[] = "{$properti}: {$nilai}";
                }

                return $aman === [] ? '' : ' style="'.implode('; ', $aman).'"';
            },
            $html,
        ) ?? $html;
    }
}
