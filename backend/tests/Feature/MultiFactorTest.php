<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Autentikasi dua faktor (M-3 pada audit keamanan).
 */
class MultiFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_totp_secret_is_not_stored_in_the_clear(): void
    {
        $orang = User::factory()->admin()->create();
        $orang->saveAppAuthenticationSecret('RAHASIA-TOTP-CONTOH');

        $mentah = DB::table('users')
            ->whereKey($orang->id)->value('app_authentication_secret');

        // Yang membaca tabel users langsung tidak boleh mendapat apa pun yang
        // bisa dipakai: pemegang secret TOTP dapat menghasilkan kode yang sah
        // kapan saja, jadi ia setara kata sandi kedua.
        $this->assertNotSame('RAHASIA-TOTP-CONTOH', $mentah);
        $this->assertStringNotContainsString('RAHASIA-TOTP', (string) $mentah);

        // Tetapi aplikasi sendiri masih bisa membacanya.
        $this->assertSame('RAHASIA-TOTP-CONTOH', $orang->fresh()->getAppAuthenticationSecret());
    }

    public function test_recovery_codes_are_not_stored_in_the_clear(): void
    {
        $orang = User::factory()->admin()->create();
        $orang->saveAppAuthenticationRecoveryCodes(['kode-satu', 'kode-dua']);

        $mentah = DB::table('users')
            ->whereKey($orang->id)->value('app_authentication_recovery_codes');

        $this->assertStringNotContainsString('kode-satu', (string) $mentah);
        $this->assertSame(['kode-satu', 'kode-dua'], $orang->fresh()->getAppAuthenticationRecoveryCodes());
    }

    /**
     * Nama yang muncul di aplikasi autentikator memakai surel, bukan nama
     * orang: satu orang bisa punya akun di beberapa sistem, dan "Budi" di
     * daftar tidak memberi tahu yang mana.
     */
    public function test_the_authenticator_entry_is_identifiable(): void
    {
        $orang = User::factory()->admin()->create(['email' => 'redaksi@contoh.test']);

        $this->assertSame('redaksi@contoh.test', $orang->getAppAuthenticationHolderName());
    }

    public function test_the_panel_still_opens_for_accounts_without_mfa(): void
    {
        // Belum diwajibkan: mewajibkannya sebelum ada yang mendaftarkan
        // perangkatnya akan mengunci seluruh redaksi di luar panelnya sendiri.
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk();
    }
}
