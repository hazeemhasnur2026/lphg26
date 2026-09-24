<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menetapkan bahasa dari grup rute yang sedang dilayani.
 *
 * Bahasanya datang dari pendaftaran rute, bukan dari menebak isi URL saat
 * runtime. Bentuk sebaliknya. Prefiks yang dihitung dari request ketika rute
 * didaftarkan. Punya dua masalah: rutenya tidak terdaftar sama sekali di dalam
 * test karena request belum ada saat aplikasi di-boot, dan `route:cache`
 * membekukan satu bahasa yang kebetulan aktif saat cache dibuat.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        /*
         * Hanya lewat pustakanya, dan tidak memanggil app()->setLocale() lebih
         * dulu. Application::setLocale() menulis ulang config('app.locale'),
         * sementara pustaka ini membaca nilai itu sekali saat dibangun untuk
         * menentukan bahasa mana yang tidak berprefiks. Mendahuluinya membuat
         * Inggris dianggap bahasa default: URL /en kehilangan prefiksnya dan
         * versi Indonesia malah ditulis /id/... di hreflang dan sitemap.
         */
        LaravelLocalization::setLocale($locale);

        return $next($request);
    }
}
