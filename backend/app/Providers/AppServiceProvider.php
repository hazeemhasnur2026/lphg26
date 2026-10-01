<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\BusinessUnit;
use App\Models\Office;
use App\Models\Post;
use App\Models\Vacancy;
use App\Support\Localization;
use Illuminate\Support\Facades\Blade;
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
         * @lurl('/page/...') menggantikan href="/page/..." yang ditulis polos
         * di seluruh view. Lihat App\Support\Localization untuk alasannya.
         */
        Blade::directive('lurl', fn (string $expression) => "<?php echo \App\Support\Localization::url({$expression}); ?>");

        /*
         * Footer tampil di tiap halaman, dan datanya tidak boleh dititipkan ke
         * setiap rute satu per satu, cukup satu composer di sini. Halaman
         * lowongan ikut lewat jalur yang sama karena disajikan rute /page
         * generik yang tidak membawa data apa pun.
         */
        /*
         * Pengumuman dibagikan ke partialnya sendiri, bukan ke seluruh layout.
         *
         * Bedanya satu kueri per permintaan: composer yang dipasang pada
         * 'layouts.app' berjalan untuk setiap halaman termasuk yang tidak
         * pernah merender pengumuman, sedangkan yang dipasang di sini hanya
         * berjalan saat partialnya benar-benar disertakan.
         */
        View::composer('partials.announcement', function ($view) {
            $view->with('announcement', Announcement::sekarang());
        });

        /*
         * Menu navigasi "Bisnis Kami" dulu ditulis tangan, tujuh tautan,
         * disalin ulang identik untuk versi mobile. Cocok hari ini karena
         * kebetulan ada tepat tujuh unit bisnis; menambah atau menghapus unit
         * lewat panel tidak akan mengubah menu ini sama sekali — situs bisa
         * menautkan ke unit yang sudah dihapus, atau melewatkan unit baru
         * tanpa satu pun galat yang terlihat.
         *
         * ordered() dan bukan urutan sisipan: itu urutan yang sama yang
         * dipakai kartu SBU di beranda, jadi menu dan beranda tidak pernah
         * berselisih urutan.
         */
        View::composer('partials.header', function ($view) {
            $view->with('businessUnits', BusinessUnit::ordered()->get(['name', 'slug']));
        });

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
         *
         * businessTabs dibangun satu kali di sini dari
         * config('site.business_unit_nav_keys'), bukan ditulis ulang di
         * ketujuh halaman our-business/*. Sebelumnya tab-strip yang sama
         * di-hardcode di tiap berkas (dua kali: prop `tabs` page-hero dan
         * pill-strip terpisah di bawahnya), jadi tujuh string terjemahan yang
         * identik harus ditulis tujuh kali. Sumbernya sekarang satu peta yang
         * sudah ada, dan label-nya memakai kunci nav.business.* yang sudah
         * diterjemahkan untuk menu navigasi.
         */
        View::composer('pages.our-business.*', function ($view) {
            $slugAktif = request()->segment(3);

            $view->with('unit', BusinessUnit::where('slug', $slugAktif)->first());
            $view->with('businessTabs', collect(config('site.business_unit_nav_keys'))
                ->map(fn (string $kunci, string $slug) => [
                    'label' => __($kunci),
                    'url' => Localization::url('/page/our-business/'.$slug),
                    'active' => $slug === $slugAktif,
                ])
                ->values()
                ->all());
        });

        View::composer('pages.career.our-vacancy', function ($view) {
            $view->with('vacancies', Vacancy::open()->get());
        });

        /*
         * multimedia.blade.php dan gallery.blade.php memakai persis tab-strip
         * yang sama dengan daftar artikel Publikasi (config('site.siblings.
         * publication')) walau keduanya halaman statis, bukan daftar artikel.
         * Dibangun sekali di sini dengan alasan yang sama seperti
         * businessTabs di atas: menghindari string terjemahan yang ditulis
         * ulang identik di tiap berkas.
         */
        View::composer('pages.publication.*', function ($view) {
            $current = '/page/publication/'.request()->segment(3);

            $view->with('publicationTabs', collect(config('site.siblings.publication', []))
                ->map(fn (array $tab) => [
                    'label' => __($tab['label']),
                    'url' => Localization::url($tab['url']),
                    'active' => $tab['url'] === $current,
                ])
                ->all());
        });
    }
}
