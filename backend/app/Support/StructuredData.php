<?php

namespace App\Support;

use App\Models\Post;

/**
 * JSON-LD untuk <head>, dibangun di PHP dan bukan di dalam blok @php di view.
 *
 * Alasannya bukan selera: Laravel 13 punya direktif Blade bernama "context",
 * dan compiler Blade memindai seluruh isi template, termasuk PHP inline di
 * dalam blok php. Kunci array '@context' tersapu jadi kode direktif, lalu
 * json_encode menyalin kode itu sebagai nama kunci. Hasilnya di beranda dan
 * tiap halaman artikel: JSON-LD dengan kunci berisi potongan PHP, sehingga
 * Google tidak mengenali tipe Organization/NewsArticle sama sekali.
 *
 * File PHP biasa tidak lewat compiler Blade, jadi '@context' aman di sini.
 */
class StructuredData
{
    private const FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public static function organization(): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Hasnur Group',
            'url' => url('/'),
            'logo' => asset('assets/img/logo.png'),
            'foundingDate' => '1966',
            // Alamat surel sengaja tidak diterbitkan di sini. JSON-LD terbaca
            // mesin, dan itu berlaku untuk pemanen alamat spam juga, sementara
            // maksud formulir Hubungi Kami justru menutup jalur itu.
            // contactPoint dengan url tetap memberi Google jalur kontak resmi.
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'url' => route('contact.show'),
                'availableLanguage' => ['id', 'en', 'ja'],
            ],
            'sameAs' => [
                'https://www.linkedin.com/company/hasnurgroup',
                'https://www.instagram.com/hasnurgroup',
                'https://www.youtube.com/@hasnurgroup',
            ],
        ], self::FLAGS);
    }

    public static function newsArticle(Post $post): string
    {
        return json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $post->title,
            // Artikel tanpa tanggal terbit memang ada di data; kunci kosong
            // dibuang ketimbang mengirim null ke Search Console.
            'datePublished' => $post->published_at?->toIso8601String(),
            'publisher' => ['@type' => 'Organization', 'name' => 'Hasnur Group'],
            'mainEntityOfPage' => $post->url,
        ]), self::FLAGS);
    }
}
