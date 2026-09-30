<?php

namespace App\Models\Concerns;

use App\Support\PengoptimalGambar;

/**
 * Model yang menyimpan path gambar pada disk `content`.
 *
 * Model yang memakai trait ini menyebut kolom gambarnya lewat kolomGambar(),
 * dan varian WebP dibuat sendiri tiap kolom itu berubah.
 *
 * Dikerjakan setelah respons terkirim, bukan sebelumnya.
 *
 * Mengubah ukuran gambar besar memakan ratusan milidetik, dan menahan tombol
 * Simpan selama itu membuat orang menekannya dua kali. Antrean sungguhan juga
 * tidak dipakai: aplikasi ini tidak menjalankan queue:work di mana pun, jadi
 * pekerjaan yang diantrekan akan mengendap selamanya — pelajaran yang sudah
 * dibayar sekali pada pemberitahuan panel.
 */
trait MengoptimalkanGambar
{
    /** @return array<int, string> nama kolom yang berisi path gambar */
    abstract public function kolomGambar(): array;

    public static function bootMengoptimalkanGambar(): void
    {
        static::saved(function ($model) {
            $berubah = array_filter(
                $model->kolomGambar(),
                fn (string $kolom) => $model->wasChanged($kolom) || $model->wasRecentlyCreated,
            );

            foreach ($berubah as $kolom) {
                $path = $model->getAttribute($kolom);

                if (blank($path)) {
                    continue;
                }

                dispatch(fn () => PengoptimalGambar::proses($path))->afterResponse();
            }
        });
    }
}
