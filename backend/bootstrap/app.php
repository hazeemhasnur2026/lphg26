<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Alias middleware mcamara/laravel-localization. Paketnya masih
         * mendokumentasikan pendaftaran lewat Kernel, yang sudah tidak ada di
         * kerangka ramping Laravel 11+. Tanpa baris ini rute bahasa gagal
         * dengan "Target class [localeSessionRedirect] does not exist".
         */
        // Berlaku untuk seluruh permintaan web, termasuk panel admin.
        $middleware->web(append: [SecurityHeaders::class]);

        /*
         * Di balik terowongan atau reverse proxy, TLS berhenti di tepi dan
         * container menerima HTTP biasa. Tanpa baris ini Laravel menganggap
         * permintaannya tidak aman, lalu membangun seluruh tautan dan aset
         * dengan skema http:// meski halamannya dilayani lewat https://.
         * Peramban memblokirnya sebagai mixed content, dan gejalanya adalah
         * halaman tanpa gaya yang tidak menyisakan galat di mana pun.
         *
         * Daftarnya sempit dengan sengaja: 192.168.65.1 adalah gerbang mesin
         * virtual Docker Desktop, satu-satunya sumber yang dilihat container
         * untuk trafik yang diteruskan dari host. Mempercayai semua alamat
         * membuat header X-Forwarded-For bisa dipalsukan, dan alamat itulah
         * yang dicatat bersama tiap pesan masuk serta dipakai pembatasan laju.
         */
        $middleware->trustProxies(at: [
            '192.168.65.0/24',
            '127.0.0.1',
        ]);

        $middleware->alias([
            'localize' => LaravelLocalizationRoutes::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
