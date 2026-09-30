<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Mengisi papan Analitik dengan peristiwa contoh.
 *
 * Gunanya satu: memperlihatkan bentuk papan itu sebelum ada pengunjung yang
 * mengklik apa pun. Papan kosong tidak bisa ditinjau — tidak ada yang bisa
 * mengatakan kolom mana yang kurang atau angka mana yang keliru dibaca saat
 * semuanya bernilai nol.
 *
 * Tiap baris yang dibuat di sini ditandai demo = true, jadi ia bisa dihitung,
 * dibedakan dari peristiwa sungguhan di papan, dan dibuang kembali secara utuh
 * lewat --hapus. Data contoh yang tidak bisa dibedakan dari data sungguhan
 * berhenti menjadi contoh dan menjadi kekeliruan.
 */
class SeedAnalyticsSample extends Command
{
    protected $signature = 'analitik:contoh
        {--hari=30 : Rentang hari ke belakang yang diisi}
        {--hapus : Hapus seluruh peristiwa contoh, jangan membuat yang baru}';

    protected $description = 'Isi papan Analitik dengan peristiwa contoh yang ditandai';

    /**
     * CTA yang bertanda data-cta di view.
     *
     * Disalin, bukan dibaca dari view: memindai Blade untuk mencari atribut
     * berarti perintah ini berhenti bekerja setiap kali ada yang mengubah
     * susunan tanda kutipnya. Bila daftarnya menyimpang dari view, yang keliru
     * hanya data contoh, dan yang sungguhan tetap benar.
     */
    private const CTA = [
        'beranda-tentang-kami',
        'beranda-lihat-bisnis',
        'beranda-lihat-semua-aktivitas',
        'kirim-pesan',
        'kirim-lamaran',
        'tonton-video',
    ];

    private const BERKAS = [
        'laporan-tahunan-2025.pdf',
        'profil-hasnur-group.pdf',
        'laporan-keberlanjutan-2025.pdf',
        'siaran-pers-peresmian-jetty.pdf',
    ];

    private const PENCARIAN = [
        'lowongan kerja',
        'laporan tahunan',
        'csr',
        'batu bara',
        'alamat kantor',
        'beasiswa',
        'hasnur riung sinergi',
        'magang',
    ];

    /*
     * Dibaca dari config('site.sosial'), bukan ditulis ulang di sini: berkas
     * ini sebelumnya menulis "hasnur-group" dengan tanda hubung untuk
     * LinkedIn, sementara dua tempat lain menulis "hasnurgroup" tanpa —
     * contoh nyata bagaimana tiga salinan mulai berselisih tanpa ada yang
     * menyadarinya, karena data seed ini tidak pernah tampil ke pengunjung.
     */
    private static function tautanLuar(): array
    {
        return array_values(config('site.sosial'));
    }

    public function handle(): int
    {
        if ($this->option('hapus')) {
            return $this->hapus();
        }

        $hari = max(1, (int) $this->option('hari'));

        $sudahAda = AnalyticsEvent::contoh()->count();

        if ($sudahAda > 0) {
            // Menolak menumpuk. Dijalankan dua kali tanpa ini, papannya
            // memperlihatkan angka dua kali lebih besar tanpa satu pun
            // peristiwa baru yang benar-benar terjadi.
            $this->warn("Sudah ada {$sudahAda} peristiwa contoh di basis data.");
            $this->line('Jalankan `php artisan analitik:contoh --hapus` lebih dulu.');

            return self::FAILURE;
        }

        // Judul artikel diambil dari isi situs yang sungguhan bila ada. Judul
        // karangan membuat papannya terlihat benar tapi tidak bisa dipakai
        // memeriksa apa pun: nama yang muncul di peringkat harus nama yang
        // memang bisa dicari orang di situsnya.
        $artikel = Post::published()->latest('published_at')->limit(12)->pluck('title')->all();
        $artikel = $artikel ?: ['Contoh artikel tanpa data situs'];

        $jalur = [
            '/',
            '/page/publication/latest-update',
            '/page/about-us/overview',
            '/page/career/our-vacancy',
            '/page/contact/contact-us',
        ];

        $baris = [];

        for ($i = $hari - 1; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);

            // Akhir pekan dibuat lebih sepi. Deret yang rata setiap hari
            // terlihat seperti data buatan pada pandangan pertama, dan papan
            // yang ditinjau dengan deret seperti itu tidak memperlihatkan
            // apakah grafiknya mampu menampilkan naik-turun.
            $pengali = $tanggal->isWeekend() ? 0.4 : 1.0;

            $porsi = [
                'cta_click' => (int) round(random_int(6, 22) * $pengali),
                'file_download' => (int) round(random_int(1, 8) * $pengali),
                'search' => (int) round(random_int(2, 12) * $pengali),
                'content_read' => (int) round(random_int(3, 16) * $pengali),
                'outbound_click' => (int) round(random_int(0, 5) * $pengali),
            ];

            foreach ($porsi as $nama => $jumlah) {
                for ($n = 0; $n < $jumlah; $n++) {
                    $baris[] = [
                        'name' => $nama,
                        'label' => $this->label($nama, $artikel),
                        'path' => $jalur[array_rand($jalur)],
                        'locale' => random_int(1, 10) === 1 ? 'en' : 'id',
                        // Sidik contoh, bukan hasil hash permintaan sungguhan.
                        // Jumlah pengunjung unik per hari dibuat lebih kecil dari
                        // jumlah peristiwanya, sebagaimana kenyataannya: satu
                        // orang mengklik lebih dari sekali.
                        'visitor' => 'contoh'.Str::padLeft((string) random_int(1, max(3, (int) ($jumlah / 2))), 6, '0').$tanggal->format('md'),
                        'demo' => true,
                        'created_at' => $tanggal->copy()
                            ->setTime(random_int(7, 21), random_int(0, 59), random_int(0, 59)),
                    ];
                }
            }
        }

        // Disisipkan per potongan, bukan satu baris per kueri. Tiga puluh hari
        // menghasilkan ribuan baris, dan ribuan kueri terpisah membuat perintah
        // ini berjalan berpuluh detik tanpa alasan.
        foreach (array_chunk($baris, 500) as $potongan) {
            AnalyticsEvent::insert($potongan);
        }

        $this->info(count($baris).' peristiwa contoh dibuat untuk '.$hari.' hari terakhir.');
        $this->line('Semuanya bertanda demo dan bisa dibuang: php artisan analitik:contoh --hapus');

        return self::SUCCESS;
    }

    private function hapus(): int
    {
        $jumlah = AnalyticsEvent::contoh()->count();

        if ($jumlah === 0) {
            $this->info('Tidak ada peristiwa contoh di basis data.');

            return self::SUCCESS;
        }

        AnalyticsEvent::contoh()->delete();

        $this->info("{$jumlah} peristiwa contoh dihapus. Peristiwa sungguhan tidak tersentuh.");

        return self::SUCCESS;
    }

    /** @param  array<int, string>  $artikel */
    private function label(string $nama, array $artikel): string
    {
        return match ($nama) {
            'cta_click' => self::CTA[array_rand(self::CTA)],
            'file_download' => self::BERKAS[array_rand(self::BERKAS)],
            'search' => self::PENCARIAN[array_rand(self::PENCARIAN)],
            'outbound_click' => self::tautanLuar()[array_rand(self::tautanLuar())],
            default => Str::limit($artikel[array_rand($artikel)], 157),
        };
    }
}
