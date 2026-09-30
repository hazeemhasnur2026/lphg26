<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Membuat varian WebP untuk gambar yang diunggah orang lewat panel.
 *
 * Padanan runtime dari tools/optimize_images.py, dan keduanya memang dibutuhkan
 * karena menangani dua hal yang berbeda sifatnya:
 *
 *   Gambar tema situs ada di git dan sudah diketahui saat build, jadi skrip
 *   Python mengerjakannya sekali di mesin pengembang.
 *
 *   Gambar unggahan baru ada setelah deploy, dan tidak ada build yang bisa
 *   menjangkaunya. Sebelum ini berarti tiap foto yang diunggah redaksi
 *   dilayani pada ukuran aslinya — foto ponsel empat megabita tetap empat
 *   megabita ke tiap pengunjung, tanpa ada yang menyadarinya sampai seseorang
 *   mengeluh situsnya lambat.
 *
 * Memakai GD, bukan memanggil cwebp lewat shell: GD sudah terpasang di image,
 * dan proses PHP yang memanggil biner luar dengan path dari basis data adalah
 * permukaan yang tidak perlu dibuka.
 */
class PengoptimalGambar
{
    /** Lebar yang sama dengan skrip Python, supaya perilakunya tidak bercabang. */
    private const LEBAR = [1600, 800, 400];

    private const KUALITAS = 82;

    /** Batas piksel yang ditolak, untuk menjaga memori proses. */
    private const MAKS_PIKSEL = 50_000_000;

    /**
     * Menghasilkan varian untuk satu berkas pada disk `content`.
     *
     * Mengembalikan false bila dilewati — bukan gambar, terlalu besar, atau
     * sudah punya varian. Tidak pernah melempar: gambar yang gagal dioptimalkan
     * tetap tampil memakai berkas aslinya, dan menggagalkan penyimpanan artikel
     * karena satu konversi gambar adalah pertukaran yang salah.
     */
    public static function proses(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        try {
            return static::kerjakan($path);
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat varian gambar.', [
                'path' => $path,
                'galat' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private static function kerjakan(string $path): bool
    {
        if (! function_exists('imagewebp')) {
            Log::warning('GD tanpa dukungan WebP; varian gambar dilewati.');

            return false;
        }

        $disk = Storage::disk('content');

        if (! $disk->exists($path)) {
            return false;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // GIF bisa beranimasi dan SVG sudah vektor: keduanya bukan urusan
        // kelas ini, sama seperti pada skrip Python.
        if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return false;
        }

        $asli = $disk->path($path);
        $ukuran = @getimagesize($asli);

        if ($ukuran === false) {
            return false;
        }

        [$lebarAsli, $tinggiAsli] = $ukuran;

        if ($lebarAsli * $tinggiAsli > self::MAKS_PIKSEL) {
            Log::warning('Gambar melewati batas piksel; varian dilewati.', ['path' => $path]);

            return false;
        }

        $sumber = match ($ext) {
            'png' => @imagecreatefrompng($asli),
            default => @imagecreatefromjpeg($asli),
        };

        if ($sumber === false) {
            return false;
        }

        // Transparansi PNG hilang tanpa dua baris ini, dan logo berlatar
        // transparan berubah menjadi kotak hitam.
        imagepalettetotruecolor($sumber);
        imagealphablending($sumber, false);
        imagesavealpha($sumber, true);

        $varian = [];

        foreach (static::lebarTarget($lebarAsli) as $lebar) {
            $tinggi = (int) round($tinggiAsli * ($lebar / $lebarAsli));
            $tujuan = static::jalurVarian($path, $lebar);

            $kanvas = imagecreatetruecolor($lebar, $tinggi);
            imagealphablending($kanvas, false);
            imagesavealpha($kanvas, true);
            imagecopyresampled($kanvas, $sumber, 0, 0, 0, 0, $lebar, $tinggi, $lebarAsli, $tinggiAsli);

            $berkas = base_path('../content-migration/images-opt/'.$tujuan);
            @mkdir(dirname($berkas), 0775, true);

            if (imagewebp($kanvas, $berkas, self::KUALITAS)) {
                $varian[] = [
                    'width' => $lebar,
                    'path' => '/content-images-opt/'.$tujuan,
                    'bytes' => filesize($berkas) ?: 0,
                ];
            }

            imagedestroy($kanvas);
        }

        imagedestroy($sumber);

        if ($varian === []) {
            return false;
        }

        ImageVariants::catat('/content-images/'.$path, [
            'width' => $lebarAsli,
            'height' => $tinggiAsli,
            'bytes' => $disk->size($path),
            // Terurut dari terkecil, bentuk yang sama dengan manifest build.
            'variants' => array_reverse($varian),
        ]);

        return true;
    }

    /**
     * Lebar varian untuk satu gambar, tanpa pernah memperbesar.
     *
     * Gambar yang aslinya lebih kecil dari lebar terkecil tetap dapat satu
     * varian pada ukuran aslinya, supaya ikon dan logo kecil tetap jadi WebP.
     */
    private static function lebarTarget(int $asli): array
    {
        $lebar = array_filter(self::LEBAR, fn (int $w) => $w < $asli);
        $lebar[] = min($asli, max(self::LEBAR));

        return array_values(array_unique($lebar));
    }

    private static function jalurVarian(string $path, int $lebar): string
    {
        $dir = dirname($path);
        $nama = pathinfo($path, PATHINFO_FILENAME);

        return ($dir === '.' ? '' : $dir.'/')."{$nama}-{$lebar}.webp";
    }
}
