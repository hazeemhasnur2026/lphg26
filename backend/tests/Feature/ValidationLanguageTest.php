<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Pesan validasi harus berbahasa Indonesia, bukan kunci mentah.
 *
 * Laravel 11 ke atas tidak lagi menyertakan berkas bahasa validasi, dan
 * aplikasi ini berjalan pada locale `id`. Tanpa lang/id/validation.php, setiap
 * galat formulir di panel muncul sebagai "validation.required" — terbaca oleh
 * orang yang paling perlu tahu apa yang salah. Ini ditemukan saat menguji
 * unggah gambar, bukan lewat pembacaan kode.
 */
class ValidationLanguageTest extends TestCase
{
    public function test_common_validation_rules_are_translated(): void
    {
        $this->app->setLocale('id');

        foreach (['required', 'email', 'image', 'unique', 'max.file', 'mimes'] as $kunci) {
            $pesan = trans("validation.{$kunci}", ['attribute' => 'berkas', 'max' => 4, 'values' => 'jpg']);

            $this->assertStringNotContainsString('validation.', $pesan,
                "Aturan '{$kunci}' tidak diterjemahkan.");
        }
    }

    public function test_required_reads_as_a_sentence(): void
    {
        $this->app->setLocale('id');

        $this->assertSame(
            'Gambar sampul wajib diisi.',
            trans('validation.required', ['attribute' => 'Gambar sampul']),
        );
    }

    public function test_login_failure_is_translated(): void
    {
        $this->app->setLocale('id');

        $pesan = trans('auth.failed');

        $this->assertStringNotContainsString('auth.', $pesan);
        $this->assertStringContainsString('tidak cocok', $pesan);
    }
}
