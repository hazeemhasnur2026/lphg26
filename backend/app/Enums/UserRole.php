<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Dua peran, bukan lebih.
 *
 * Pembedaan yang benar-benar dibutuhkan situs ini hanya satu: siapa yang boleh
 * membaca pesan dari pengunjung. Isi pesan itu data pribadi, dan tidak semua
 * orang yang menyunting artikel perlu melihatnya. Menambah peran ketiga tanpa
 * ada yang memintanya hanya menambah hal yang harus dijaga.
 */
enum UserRole: string implements HasLabel
{
    case Administrator = 'administrator';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Editor => 'Editor Konten',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Administrator => 'Seluruh menu, termasuk pesan masuk dan pengelolaan pengguna.',
            self::Editor => 'Artikel, unit bisnis, slider, kantor, dan lowongan. Tidak dapat membuka pesan masuk.',
        };
    }
}
