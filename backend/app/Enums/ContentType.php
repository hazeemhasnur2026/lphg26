<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Tipe konten menurut TOR.
 *
 * Perlu dibedakan dari `category`, yang sudah lebih dulu ada dan tidak boleh
 * diganti: kategori menentukan URL artikel (/post/{kategori}/{slug}) dan
 * halaman daftar tempat ia muncul. Tiga puluh lima artikel hasil migrasi sudah
 * terindeks Google dengan bentuk URL itu; mengubahnya berarti mematikan tautan
 * yang sudah ada demi kerapian model data.
 *
 * Jadi keduanya hidup berdampingan dan menjawab pertanyaan berbeda: kategori
 * menjawab "di halaman mana ini muncul", tipe menjawab "ini bentuk konten apa".
 * Sebuah Laporan Keberlanjutan dan sebuah siaran pers bisa sama-sama tampil di
 * daftar Berita, tapi keduanya bukan barang yang sama.
 *
 * Business dan Career tidak ada di sini karena keduanya bukan artikel: masing-
 * masing sudah punya modelnya sendiri, BusinessUnit dan Vacancy.
 */
enum ContentType: string implements HasLabel
{
    case News = 'news';
    case PressRelease = 'press_release';
    case Story = 'story';
    case Insight = 'insight';
    case Publication = 'publication';
    case Report = 'report';
    case Gallery = 'gallery';
    case Video = 'video';
    case Sustainability = 'sustainability';

    public function getLabel(): string
    {
        return match ($this) {
            self::News => 'Berita',
            self::PressRelease => 'Siaran Pers',
            self::Story => 'Cerita',
            self::Insight => 'Wawasan',
            self::Publication => 'Publikasi',
            self::Report => 'Laporan',
            self::Gallery => 'Galeri',
            self::Video => 'Video',
            self::Sustainability => 'Keberlanjutan',
        };
    }

    /** Tipe yang isinya sebuah berkas untuk diunduh, bukan badan artikel. */
    public function isDocument(): bool
    {
        return in_array($this, [self::Publication, self::Report], true);
    }

    /**
     * Tebakan tipe dari kategori, dipakai sekali saat migrasi mengisi kolom
     * baru untuk artikel yang sudah ada. Bukan aturan yang berlaku seterusnya:
     * sesudah ini tipe dipilih orang, dan satu kategori boleh memuat banyak
     * tipe.
     */
    public static function fromCategory(?string $category): self
    {
        return match ($category) {
            'press-release' => self::PressRelease,
            'csr-activity', 'she-activity' => self::Sustainability,
            default => self::News,
        };
    }
}
