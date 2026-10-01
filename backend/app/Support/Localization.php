<?php

namespace App\Support;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Tautan internal yang ikut bahasa yang sedang dibuka.
 *
 * href="/page/..." yang ditulis polos selalu menunjuk versi Indonesia, karena
 * itu rute yang terdaftar tanpa prefiks. Pengunjung yang sedang di /en/...
 * yang mengeklik tautan semacam itu terlempar balik ke bahasa Indonesia.
 * Dipusatkan di sini, satu tempat, dengan pola yang sama seperti yang sudah
 * dipakai benar di Post::getUrlAttribute().
 */
class Localization
{
    public static function url(string $path): string
    {
        return LaravelLocalization::getLocalizedURL(app()->getLocale(), url($path));
    }
}
