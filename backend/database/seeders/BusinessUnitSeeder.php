<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use Illuminate\Database\Seeder;

/**
 * Tujuh SBU, disalin apa adanya dari kartu yang sebelumnya ditulis tangan di
 * home.blade.php. Nama, deskripsi, foto, ikon, dan dua warna per kartu yang
 * berasal dari panduan merek resmi.
 *
 * Dijalankan pakai updateOrCreate pada slug, jadi seeding ulang tidak
 * menduplikasi dan tidak menimpa urutan yang mungkin diubah dari admin nanti.
 */
class BusinessUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'name' => 'Logistik',
                'slug' => 'logistic',
                'icon' => 'bisnis/1.logistics.png',
                'image' => 'pages/our-business-logistic/banner.png',
                'image_alt' => 'Kapal dan pelabuhan logistik Hasnur Group',
                'tint_color' => '#EBF6FB',
                'accent_color' => '#008CC8',
                'short_description' => 'Mendukung kebutuhan logistik internal grup maupun mitra eksternal.',
                'order' => 0,
            ],
            [
                'name' => 'Agribisnis & Kehutanan',
                'slug' => 'agribusiness-forestry',
                'icon' => 'bisnis/2.agro-forestry.png',
                'image' => 'pages/our-business-agribusiness-forestry/banner.png',
                'image_alt' => 'Hamparan perkebunan kelapa sawit',
                'tint_color' => '#F5FBF0',
                'accent_color' => '#5F9A28',
                'short_description' => 'Berawal dari jasa penarikan rakit kayu di DAS Barito, kini mencakup perkebunan dan kehutanan.',
                'order' => 1,
            ],
            [
                'name' => 'Energi',
                'slug' => 'energy',
                'icon' => 'bisnis/3.energy.png',
                'image' => 'pages/our-business-energy/banner.png',
                'image_alt' => 'Batubara hasil pertambangan',
                'tint_color' => '#FFFDEB',
                'accent_color' => '#B38A00',
                'short_description' => 'Fokus pertambangan dengan visi menjadi pemain terkemuka di Indonesia, kapasitas produksi terus tumbuh.',
                'order' => 2,
            ],
            [
                'name' => 'Teknologi & Jasa',
                'slug' => 'technology-services',
                'icon' => 'bisnis/4.technology-services.png',
                'image' => 'pages/our-business-technology-services/banner.png',
                'image_alt' => 'Kantor unit teknologi digital Hasnur Group',
                'tint_color' => '#F5F0EF',
                'accent_color' => '#80493A',
                'short_description' => 'Menaungi unit bisnis pengembangan teknologi dan layanan.',
                'order' => 3,
            ],
            [
                'name' => 'Pendidikan',
                'slug' => 'education',
                'icon' => 'bisnis/5.education.png',
                'image' => 'pages/our-business-education/banner.jpg',
                'image_alt' => 'Gedung sekolah Hasnur',
                'tint_color' => '#F7F0F5',
                'accent_color' => '#99477F',
                'short_description' => 'Mengembangkan pendidikan bertaraf internasional dengan pendekatan kearifan lokal dan religius.',
                'order' => 4,
            ],
            [
                'name' => 'Kebutuhan Pelanggan (Consumer)',
                'slug' => 'consumer',
                'icon' => 'bisnis/6.consumer.png',
                'image' => 'pages/our-business-consumer/banner.png',
                'image_alt' => 'Tim PS Barito Putera',
                'tint_color' => '#FDF4EE',
                'accent_color' => '#D96412',
                'short_description' => 'Membawahi bisnis media, gaya hidup, dan kesehatan.',
                'order' => 5,
            ],
            [
                'name' => 'Investasi',
                'slug' => 'investment',
                'icon' => 'bisnis/7.investment.png',
                'image' => 'pages/our-business-investment/banner.png',
                'image_alt' => 'Grafik pergerakan investasi',
                'tint_color' => '#FCEDEE',
                'accent_color' => '#DA2127',
                'short_description' => 'Mendukung pengembangan bisnis grup melalui investasi internal maupun eksternal.',
                'order' => 6,
            ],
        ];

        foreach ($units as $unit) {
            BusinessUnit::updateOrCreate(['slug' => $unit['slug']], $unit);
        }
    }
}
