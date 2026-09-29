<?php

namespace App\Providers;

use App\Models\BusinessUnit;
use App\Models\Office;
use App\Models\Post;
use App\Models\Vacancy;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * Footer tampil di tiap halaman, dan datanya tidak boleh dititipkan ke
         * setiap rute satu per satu, cukup satu composer di sini. Halaman
         * lowongan ikut lewat jalur yang sama karena disajikan rute /page
         * generik yang tidak membawa data apa pun.
         */
        View::composer('partials.footer', function ($view) {
            $view->with([
                'headOffice' => Office::headquarters()->ordered()->first(),
                'branchOffices' => Office::representative()->ordered()->get(),
                // Sebelumnya tiga judul ditulis tangan di footer. Tidak satu
                // pun cocok dengan judul artikel di database. Dua parafrase
                // terpotong, dan satu ("Top CSR Awards 2026") merujuk artikel
                // yang tidak ada; yang nyata bertahun 2025 dan ikut terhapus.
                // Ketiganya juga menautkan ke halaman daftar, bukan artikelnya.
                //
                // published() wajib di sini, dan tempat ini paling mudah
                // terlewat: footer muncul di setiap halaman, jadi draf yang
                // bocor lewat daftar ini bocor di seluruh situs sekaligus —
                // termasuk di halaman yang daftarnya sendiri sudah disaring
                // dengan benar.
                'footerPosts' => Post::published()
                    ->orderByRaw('published_at IS NULL, published_at DESC, id DESC')
                    ->limit(3)
                    ->get(),
            ]);
        });

        /*
         * Halaman rinci tiap SBU masih view Blade, tapi tautan ke situs resminya
         * datang dari database. Slug diambil dari URL karena rute /page generik
         * tidak membawa model apa pun.
         */
        View::composer('pages.our-business.*', function ($view) {
            $view->with('unit', BusinessUnit::where('slug', request()->segment(3))->first());
        });

        View::composer('pages.career.our-vacancy', function ($view) {
            $view->with('vacancies', Vacancy::open()->get());
        });
    }
}
