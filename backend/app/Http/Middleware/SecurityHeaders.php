<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk setiap respons.
 *
 * Empat header pertama tidak bisa merusak apa pun: keduanya hanya melarang hal
 * yang memang tidak dilakukan situs ini. HSTS hanya dikirim ketika permintaannya
 * sudah lewat HTTPS, supaya pengembangan lokal di http tidak ikut terkunci ke
 * https selama setahun di peramban pengembang.
 *
 * CSP dikirim sebagai Report-Only, bukan penegakan, dan itu keputusan sadar:
 *
 *   - Alpine mengevaluasi ekspresi x-data lewat new Function(), jadi penegakan
 *     tanpa 'unsafe-eval' mematikan slider, menu, pemilih bahasa, dan roda nilai
 *     inti sekaligus.
 *   - Panel admin memuat tiga blok <script> inline dari Livewire.
 *   - Ada 102 atribut style inline di beranda, sebagian membawa warna merek yang
 *     datang dari database.
 *
 * Kebijakan di bawah sudah mencerminkan keadaan itu. Sebelum dinaikkan menjadi
 * penegakan, buka situs dan panel admin di peramban, periksa laporan pelanggaran
 * di konsol, lalu ganti nama headernya. Menaikkannya tanpa memeriksa berarti
 * mengganti risiko XSS dengan risiko situs yang tidak berfungsi.
 */
class SecurityHeaders
{
    private const POLICY = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-eval'",
        "style-src 'self' 'unsafe-inline'",
        "img-src 'self' data:",
        "font-src 'self'",
        "connect-src 'self'",
        "form-action 'self'",
        "frame-ancestors 'none'",
        "object-src 'none'",
        "base-uri 'self'",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        // Peramban dilarang menebak tipe konten. Berkas unggahan dilayani dari
        // domain yang sama, jadi tebakan yang salah bisa mengeksekusi berkas.
        $headers->set('X-Content-Type-Options', 'nosniff');

        // Situs ini tidak pernah dipasang di dalam bingkai situs lain.
        $headers->set('X-Frame-Options', 'DENY');

        // URL internal tidak ikut terkirim ke situs tujuan saat pengunjung
        // mengeklik tautan keluar.
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Situs ini tidak memakai kamera, mikrofon, maupun lokasi.
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');

        $headers->set('Content-Security-Policy-Report-Only', implode('; ', self::POLICY));

        if ($request->secure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
