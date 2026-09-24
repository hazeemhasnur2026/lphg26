<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Konten tetap yang dulu ditulis langsung di Blade.
     *
     * Artikel sengaja tidak ada di sini: sumbernya hasil crawl situs lama, dan
     * `php artisan import:articles` yang mengurusnya. Lowongan juga tidak,
     * situs lama tidak pernah memuat satu lowongan pun, dan mengarang isi
     * lowongan lebih buruk daripada halaman yang jujur kosong.
     */
    public function run(): void
    {
        $this->call([
            BusinessUnitSeeder::class,
            OfficeSeeder::class,
            SlideBannerSeeder::class,
        ]);
    }
}
