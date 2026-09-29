<?php

namespace App\Support;

/**
 * Tautan video yang diketik redaksi, diubah menjadi bentuk yang bisa ditanam.
 *
 * Yang disimpan di basis data adalah tautan yang orang salin dari bilah alamat,
 * karena itu yang mereka punya. Bentuk itu tidak bisa dipakai di dalam iframe:
 * youtube.com/watch?v=xxx akan menolak dimuat, dan yang tampil hanya kotak
 * kosong tanpa galat apa pun di konsol.
 *
 * Penerjemahan dilakukan saat menampilkan, bukan saat menyimpan. Kalau bentuk
 * tanam yang disimpan, tautan yang dibuka orang dari panel admin tidak lagi
 * sama dengan yang mereka masukkan, dan aturan penyedia yang berubah berarti
 * seluruh baris lama harus ditulis ulang.
 */
class VideoEmbed
{
    /**
     * URL untuk iframe, atau null bila tautannya bukan penyedia yang dikenali.
     *
     * Null dijawab dengan tautan biasa di view, bukan dengan iframe kosong:
     * lebih baik pembaca menekan tautan yang bekerja daripada menatap kotak
     * abu-abu yang tidak menjelaskan apa-apa.
     */
    public static function url(?string $tautan): ?string
    {
        if (blank($tautan)) {
            return null;
        }

        $bagian = parse_url($tautan);
        $host = str_replace('www.', '', strtolower($bagian['host'] ?? ''));
        $path = trim($bagian['path'] ?? '', '/');
        parse_str($bagian['query'] ?? '', $kueri);

        return match (true) {
            $host === 'youtu.be' => static::youtube($path),
            $host === 'youtube.com' && isset($kueri['v']) => static::youtube($kueri['v']),
            // Shorts dan embed: keduanya sudah membawa id di path.
            $host === 'youtube.com' && str_starts_with($path, 'shorts/') => static::youtube(substr($path, 7)),
            $host === 'youtube.com' && str_starts_with($path, 'embed/') => static::youtube(substr($path, 6)),
            $host === 'vimeo.com' && ctype_digit($path) => "https://player.vimeo.com/video/{$path}",
            default => null,
        };
    }

    /** Berkas video yang dilayani sendiri, ditampilkan dengan <video>, bukan iframe. */
    public static function isBerkas(?string $tautan): bool
    {
        return filled($tautan)
            && preg_match('/\.(mp4|webm|ogg)($|\?)/i', $tautan) === 1;
    }

    private static function youtube(string $id): ?string
    {
        // Id YouTube selalu 11 karakter dari kumpulan yang sempit. Memeriksanya
        // menutup jalan bagi nilai apa pun dari basis data untuk masuk begitu
        // saja ke atribut src sebuah iframe.
        $id = strtok($id, '?&');

        return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) === 1
            ? "https://www.youtube-nocookie.com/embed/{$id}"
            : null;
    }
}
