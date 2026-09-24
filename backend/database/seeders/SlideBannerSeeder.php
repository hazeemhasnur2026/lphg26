<?php

namespace Database\Seeders;

use App\Models\SlideBanner;
use Illuminate\Database\Seeder;

/**
 * Lima slide galeri beranda, dipindahkan dari array yang sebelumnya ditulis
 * tangan di assets/js/main.js.
 *
 * Fotonya masih dokumentasi lama yang dipakai sementara, dan resolusinya hanya
 * 550x309 px padahal dipasang full-bleed 28:9, jadi tetap perlu foto pengganti
 * yang lebih besar. Yang berubah sekarang: penggantinya bisa diunggah dari
 * admin, tidak perlu menyunting berkas JavaScript.
 *
 * Yang disimpan adalah path gambar master relatif terhadap disk `assets`, bukan
 * varian WebP. Pemilihan varian terjadi saat render (lihat routes/web.php), jadi
 * kolom ini tetap berisi berkas yang diunggah orang, bukan turunan konversi.
 */
class SlideBannerSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'image' => 'gallery/01-hasnur-group-she-activity.jpg',
                'image_alt' => 'Aktivitas SHE Hasnur Group',
                'title' => 'Aktivitas SHE',
                'subtitle' => 'Komitmen keselamatan, kesehatan, dan lingkungan di seluruh unit operasional.',
                'order' => 0,
            ],
            [
                'image' => 'gallery/02-hasnur-group-csr-activity.jpg',
                'image_alt' => 'Aktivitas CSR Hasnur Group',
                'title' => 'Aktivitas CSR',
                'subtitle' => 'Program tanggung jawab sosial perusahaan untuk masyarakat sekitar.',
                'order' => 1,
            ],
            [
                'image' => 'gallery/04-hasnur-group-memberikan-bantuan-banjir-k.jpg',
                'image_alt' => 'Bantuan penanganan banjir',
                'title' => 'Tanggap Bencana',
                'subtitle' => 'Bantuan penanganan banjir bagi masyarakat terdampak.',
                'order' => 2,
            ],
            [
                'image' => 'gallery/05-yayasan-hasnur-centre-menyerahkan-bantua.jpg',
                'image_alt' => 'Yayasan Hasnur Centre menyerahkan bantuan',
                'title' => 'Yayasan Hasnur Centre',
                'subtitle' => 'Penyaluran bantuan sosial melalui yayasan yang didirikan founder.',
                'order' => 3,
            ],
            [
                'image' => 'gallery/09-she-hasnur-group-tanggap-darurat-di-sbu.jpg',
                'image_alt' => 'SHE tanggap darurat di SBU',
                'title' => 'Tanggap Darurat',
                'subtitle' => 'Kesiapsiagaan tim SHE di seluruh Strategic Business Unit.',
                'order' => 4,
            ],
        ];

        foreach ($slides as $slide) {
            SlideBanner::updateOrCreate(['image' => $slide['image']], $slide);
        }
    }
}
