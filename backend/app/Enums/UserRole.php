<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Peran pengguna panel.
 *
 * Sebelumnya hanya dua, Administrator dan Editor, dan pembedanya satu hal:
 * siapa yang boleh membaca pesan dari pengunjung. Itu cukup selama menerbitkan
 * artikel tidak perlu persetujuan siapa pun.
 *
 * TOR meminta alur Draf → Tinjau → Setujui → Terbit, dan alur itu tidak berarti
 * apa-apa bila setiap orang boleh menerbitkan naskahnya sendiri. Maka peran di
 * sini dibagi menurut satu pertanyaan: seberapa jauh naskah boleh didorong oleh
 * orang ini sendirian.
 *
 *   Kontributor  menulis dan mengajukan, tidak bisa menyetujui tulisannya.
 *   Peninjau     menyetujui dan menerbitkan, tidak mengurus pengguna.
 *   Admin        seluruh konten, termasuk pesan masuk dari pengunjung.
 *   Super Admin  semuanya, termasuk membuat dan menghapus akun.
 *
 * Pemetaan dari peran lama: Administrator menjadi Super Admin, Editor menjadi
 * Admin. Keduanya naik, bukan turun. Menurunkan hak akun yang sedang dipakai
 * berarti mengunci orang di luar panelnya sendiri saat memperkenalkan sistem
 * peran, dan tidak ada cara memperbaikinya dari dalam panel.
 */
enum UserRole: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Reviewer = 'reviewer';
    case Contributor = 'contributor';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin Korporat',
            self::Reviewer => 'Peninjau',
            self::Contributor => 'Kontributor',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Seluruh menu, termasuk pengelolaan pengguna dan pengaturan situs.',
            self::Admin => 'Seluruh konten dan pesan masuk. Tidak dapat mengelola akun.',
            self::Reviewer => 'Meninjau, menyetujui, dan menerbitkan konten. Tidak dapat membaca pesan masuk.',
            self::Contributor => 'Menulis dan mengajukan konten sendiri. Tidak dapat menerbitkan.',
        };
    }

    /** Boleh mendorong naskah melewati tinjauan. */
    public function canApprove(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Reviewer], true);
    }

    /** Boleh menerbitkan dan menarik konten dari situs. */
    public function canPublish(): bool
    {
        return $this->canApprove();
    }

    /**
     * Boleh membaca pesan dari formulir Hubungi Kami.
     *
     * Isinya data pribadi pengunjung, jadi daftarnya sengaja pendek. Peninjau
     * tidak masuk: pekerjaannya membaca naskah, bukan korespondensi.
     */
    public function canReadContactMessages(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    /** Boleh membuat, mengubah, dan menghapus akun panel. */
    public function canManageUsers(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Boleh menyunting konten milik orang lain.
     *
     * Kontributor hanya memegang naskahnya sendiri. Tanpa batas ini, peran itu
     * tidak berbeda dari admin selain pada tombol terbit.
     */
    public function canEditOthersContent(): bool
    {
        return $this !== self::Contributor;
    }
}
