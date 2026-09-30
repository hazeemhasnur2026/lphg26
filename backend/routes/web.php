<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetLocale;
use App\Models\BusinessUnit;
use App\Models\Post;
use App\Models\SlideBanner;
use App\Support\ImageVariants;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
 * Semua halaman publik dibungkus grup bahasa dari mcamara/laravel-localization.
 *
 * Bahasa Indonesia sengaja tetap di akar URL (hideDefaultLocaleInURL): setiap
 * tautan yang sudah terindeks Google memakai bentuk itu, dan memberinya prefiks
 * /id akan memutus semuanya sekaligus. Inggris dan Jepang mendapat /en dan /ja.
 */
/*
 * Halaman publik didaftarkan satu kali per bahasa.
 *
 * Bahasa Indonesia tetap di akar URL tanpa prefiks: setiap tautan yang sudah
 * terindeks Google memakai bentuk itu. Inggris dan Jepang mendapat /en dan /ja,
 * dan nama rutenya diberi awalan bahasa supaya tidak saling menimpa.
 */
$publicRoutes = function () {
    Route::get('/', function () {
        return view('home', [
            'latestPosts' => Post::published()
                ->orderByRaw('published_at IS NULL, published_at DESC, id DESC')
                ->limit(3)
                ->get(),
            'businessUnits' => BusinessUnit::ordered()->get(),
            // Dibentuk ulang ke bentuk yang dibaca createCarousel() di main.js,
            // supaya nama kolom database tidak merembes ke kontrak JavaScript.
            //
            // Varian WebP dipilih di sini, bukan disimpan di kolom image: yang
            // disimpan tetap gambar master, itu yang diunggah orang dari admin
            // nanti, dan pipeline yang mengurus ukuran. Slider memakai
            // <template x-for> yang tidak bisa memuat <picture>, jadi hanya satu
            // URL yang mungkin dan tidak ada fallback untuk browser tanpa WebP.
            'slides' => SlideBanner::active()->get()->map(fn (SlideBanner $slide) => [
                'src' => ImageVariants::best($slide->image_url, 1600),
                'alt' => $slide->image_alt,
                'title' => $slide->title,
                'subtitle' => $slide->subtitle,
            ])->all(),
        ]);
    })->name('home');

    /*
     * Every URL below matches the old site exactly, so nothing already indexed
     * needs a redirect after the cutover.
     */

    // Listings that read from the posts table. These replaced pages whose article
    // cards used to be written by hand.
    Route::get('/page/{section}/{category}', [PostController::class, 'index'])
        ->where('section', 'publication|csr|she')
        ->whereIn('category', ['latest-update', 'news', 'press-release', 'csr-activity', 'she-activity'])
        ->name('post.index');

    Route::get('/post/{category}/{slug}', [PostController::class, 'show'])->name('post.show');

    // Pencarian situs. Di dalam grup bahasa supaya hasilnya mengikuti bahasa
    // yang sedang dibuka, sama seperti halaman daftar.
    Route::get('/cari', SearchController::class)->name('search');

    /*
     * Nilai Inti pindah ke beranda; halaman tersendirinya dihapus.
     *
     * 301, bukan 404 atau 410: isinya tidak hilang, hanya berpindah tempat,
     * itulah arti pengalihan permanen. URL ini sudah terindeks sejak situs lama,
     * dan Google akan memindahkan peringkatnya ke tujuan barunya.
     *
     * Harus di atas rute /page/{group}/{slug}: pola itu ikut cocok dengan URL
     * ini dan akan menjawab 404 lebih dulu.
     */
    Route::get('/page/about-us/core-value', fn () => redirect(
        // Mengikuti bahasa yang sedang dibuka; tujuan tetap akan melempar
        // pembaca /en kembali ke beranda Indonesia.
        LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/')).'#nilai-inti', 301
    ))->name('core-value.moved');

    /*
     * Hubungi Kami. Alamat surel tidak lagi ditulis sebagai tautan mailto di
     * navigasi: alamat yang terpampang di HTML adalah yang pertama dipanen pengirim
     * spam, dan lewat formulir pesannya sekaligus tersimpan.
     *
     * Harus berada di atas rute /page/{group}/{slug} di bawah ini. Pola itu ikut
     * cocok dengan URL ini, dan rute pertama yang cocok yang dipakai. Didaftarkan
     * setelahnya, halaman ini tidak pernah terpanggil dan yang muncul 404.
     */
    Route::get('/page/contact/contact-us', [ContactController::class, 'show'])->name('contact.show');
    Route::post('/page/contact/contact-us', [ContactController::class, 'store'])
        // Cukup longgar untuk orang yang salah ketik lalu mengirim ulang, cukup
        // ketat untuk membuat pengiriman massal tidak ada gunanya.
        ->middleware('throttle:5,10')
        ->name('contact.store');

    // Fixed-content pages. One route reads config/site.php rather than twenty route
    // lines, so adding a page means adding a view and one entry there.

    Route::get('/page/{group}/{slug}', function (string $group, string $slug) {
        $view = config("site.pages.{$group}/{$slug}");
        abort_unless($view, 404);

        return view($view);
    })->name('page.show');
};

foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale) {
    $isDefault = $locale === config('app.locale');

    Route::prefix($isDefault ? '' : $locale)
        ->name($isDefault ? '' : "{$locale}.")
        ->middleware(SetLocale::class.':'.$locale)
        ->group($publicRoutes);
}

// Di luar grup bahasa: satu berkas untuk seluruh situs, dan URL di dalamnya
// sudah absolut lengkap dengan prefiks bahasanya masing-masing.
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
