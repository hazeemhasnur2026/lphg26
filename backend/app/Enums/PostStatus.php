<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Tahap sebuah artikel dalam alur redaksi.
 *
 * Sebelum ini status artikel adalah biner dan tersembunyi: published_at kosong
 * berarti draf, terisi berarti terbit. Bentuk itu cukup selama hanya satu orang
 * yang menulis sekaligus menerbitkan, dan runtuh begitu ada yang menyetujui.
 * Tanggal terbit tidak bisa membedakan naskah yang belum selesai ditulis dari
 * naskah yang sudah selesai tapi belum disetujui, padahal dua-duanya "belum
 * terbit" dan hanya satu yang menunggu orang lain.
 *
 * published_at tetap ada dan tetap menentukan kapan artikel muncul. Yang
 * ditambahkan status adalah jawaban atas pertanyaan yang berbeda: siapa yang
 * sekarang memegang naskah ini.
 */
enum PostStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::InReview => 'Menunggu Tinjauan',
            self::Approved => 'Disetujui',
            self::Published => 'Terbit',
            self::Archived => 'Diarsipkan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InReview => 'warning',
            self::Approved => 'info',
            self::Published => 'success',
            self::Archived => 'danger',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => 'Masih ditulis. Hanya penulisnya dan redaksi yang melihat.',
            self::InReview => 'Sudah diajukan penulisnya, menunggu peninjau membaca.',
            self::Approved => 'Lolos tinjauan, menunggu diterbitkan atau menunggu jadwalnya tiba.',
            self::Published => 'Tampil di situs, selama tanggal terbitnya sudah lewat.',
            self::Archived => 'Ditarik dari situs. Isinya tetap tersimpan dan bisa dikembalikan.',
        };
    }

    /**
     * Status yang boleh dituju dari status ini.
     *
     * Ditulis sebagai daftar, bukan rangkaian if, supaya panel dan tes membaca
     * aturan yang sama persis. Artikel terbit boleh kembali ke draf: itu yang
     * terjadi saat ada kekeliruan yang harus ditarik dulu sebelum diperbaiki.
     */
    public function nextStates(): array
    {
        return match ($this) {
            self::Draft => [self::InReview],
            self::InReview => [self::Approved, self::Draft],
            self::Approved => [self::Published, self::Draft],
            self::Published => [self::Archived, self::Draft],
            self::Archived => [self::Draft, self::Published],
        };
    }
}
