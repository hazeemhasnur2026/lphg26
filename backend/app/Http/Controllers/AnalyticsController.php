<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Penerima peristiwa analitik dari halaman publik (CMS-10).
 *
 * Satu rute, satu metode, tanpa keluaran. Skrip di halaman tidak menunggu
 * jawaban dan tidak punya apa pun untuk dilakukan dengannya: 204 lebih jujur
 * daripada badan JSON yang tidak pernah dibaca, dan lebih murah pada halaman
 * yang mengirim peristiwa tepat saat pembaca sedang meninggalkannya.
 */
class AnalyticsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Setelan mati berarti benar-benar tidak mencatat, bukan mencatat
        // diam-diam lalu menyembunyikan papannya. Dibalas 204 juga, bukan 404:
        // yang bertanya adalah skrip, dan galat di konsol pengunjung tidak
        // memperbaiki apa pun.
        if (! config('services.analytics.internal')) {
            return response()->noContent();
        }

        $data = $request->validate([
            'nama' => ['required', 'string', Rule::in(array_keys(AnalyticsEvent::PERISTIWA))],
            // Batas panjang di sini jauh di atas panjang kolom; pemotongannya
            // terjadi di model. Aturan ini hanya menolak muatan yang besarnya
            // sudah tidak masuk akal untuk sebuah label.
            'label' => ['nullable', 'string', 'max:500'],
            'jalur' => ['nullable', 'string', 'max:500'],
            'bahasa' => ['nullable', 'string', 'max:10'],
        ]);

        AnalyticsEvent::catat(
            $data['nama'],
            $data['label'] ?? null,
            $this->jalur($data['jalur'] ?? null),
            $data['bahasa'] ?? null,
            $request,
        );

        return response()->noContent();
    }

    /**
     * Mengambil bagian jalur dari apa pun yang dikirim halaman.
     *
     * Nilainya datang dari browser, jadi ia bisa berisi apa saja. Yang disimpan
     * dibatasi pada jalur dan kueri sebuah URL: host dibuang karena seluruh
     * baris di tabel ini berasal dari situs ini sendiri, dan URL lengkap dari
     * domain lain di kolom itu hanya akan menyesatkan yang membacanya nanti.
     */
    private function jalur(?string $nilai): ?string
    {
        if (blank($nilai)) {
            return null;
        }

        $jalur = parse_url($nilai, PHP_URL_PATH) ?: '/';
        $kueri = parse_url($nilai, PHP_URL_QUERY);

        return $kueri ? "{$jalur}?{$kueri}" : $jalur;
    }
}
