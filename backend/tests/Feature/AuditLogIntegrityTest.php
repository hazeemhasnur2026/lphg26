<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Jejak audit hanya boleh bertambah (L-3 pada audit keamanan).
 *
 * AuditLogResource sudah menolak sunting dan hapus, tetapi itu penjagaan di
 * lapisan aplikasi: satu perintah di tinker, satu artisan command yang ditulis
 * tergesa, atau satu rute baru yang lupa memeriksa melewatinya tanpa hambatan.
 *
 * Jejak audit yang bisa dirapikan oleh orang yang paling berkepentingan
 * merapikannya tidak menjawab apa pun pada saat ia justru dibutuhkan.
 */
class AuditLogIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function jalurTulis(): array
    {
        return [
            'Eloquent update' => ['eloquent-update'],
            'Eloquent delete' => ['eloquent-delete'],
            'query builder update' => ['qb-update'],
            'query builder delete' => ['qb-delete'],
            'SQL mentah update' => ['raw-update'],
            'SQL mentah delete' => ['raw-delete'],
        ];
    }

    #[DataProvider('jalurTulis')]
    public function test_no_write_path_can_alter_the_trail(string $jalur): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Trigger hanya dipasang pada SQLite; pada MySQL hak akses yang dicabut.');
        }

        $baris = AuditLog::create(['action' => 'uji', 'description' => 'Asli']);
        $id = $baris->id;

        try {
            match ($jalur) {
                'eloquent-update' => AuditLog::find($id)->update(['description' => 'Diubah']),
                'eloquent-delete' => AuditLog::find($id)->delete(),
                'qb-update' => DB::table('audit_logs')->where('id', $id)->update(['description' => 'Diubah']),
                'qb-delete' => DB::table('audit_logs')->where('id', $id)->delete(),
                'raw-update' => DB::statement('UPDATE audit_logs SET description = ? WHERE id = ?', ['Diubah', $id]),
                'raw-delete' => DB::statement('DELETE FROM audit_logs WHERE id = ?', [$id]),
            };
        } catch (\Throwable) {
            // Diharapkan: trigger membatalkan operasinya.
        }

        $akhir = AuditLog::find($id);

        $this->assertNotNull($akhir, "Baris terhapus lewat {$jalur}.");
        $this->assertSame('Asli', $akhir->description, "Baris berubah lewat {$jalur}.");
    }

    /**
     * Menghapus akun tetap harus bisa.
     *
     * Penghapusan memicu foreign key nullOnDelete, yang berarti satu UPDATE
     * pada tabel ini. Trigger yang menolak setiap UPDATE membuat akun tidak
     * bisa dihapus sama sekali — jejak audit yang dijaga terlalu ketat berubah
     * menjadi kebuntuan operasional. Ini ditemukan oleh tes, bukan oleh
     * pembacaan ulang.
     */
    public function test_deleting_an_account_still_works_and_keeps_the_trail(): void
    {
        $orang = User::factory()->admin()->create(['name' => 'Rina']);
        $this->actingAs($orang);

        AuditLog::record('post.updated', null, 'Sesuatu diubah');
        $id = AuditLog::latest('id')->value('id');

        $orang->delete();

        $jejak = AuditLog::find($id);

        $this->assertNotNull($jejak, 'Jejak ikut terhapus bersama akunnya.');
        $this->assertNull($jejak->user_id);
        // Namanya disalin saat peristiwa terjadi, jadi jejaknya tetap menjawab
        // "siapa" setelah akunnya hilang.
        $this->assertSame('Rina', $jejak->user_name);
        $this->assertSame('Sesuatu diubah', $jejak->description);
    }

    /**
     * Pengecualian untuk foreign key tidak boleh menjadi pintu belakang:
     * menulis user_id menjadi NULL sambil mengubah kolom lain harus tetap
     * ditolak.
     */
    public function test_the_foreign_key_exception_cannot_smuggle_other_changes(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Trigger hanya dipasang pada SQLite.');
        }

        // Dibuat sudah membawa user_id: menyetelnya belakangan lewat UPDATE
        // akan ditolak trigger, dan yang ingin diuji di sini bukan itu.
        $pemilik = User::factory()->admin()->create();
        $baris = AuditLog::create([
            'action' => 'uji', 'description' => 'Asli', 'user_id' => $pemilik->id,
        ]);

        try {
            DB::table('audit_logs')->where('id', $baris->id)
                ->update(['user_id' => null, 'description' => 'Diselundupkan']);
        } catch (\Throwable) {
            // Diharapkan.
        }

        $this->assertSame('Asli', AuditLog::find($baris->id)->description);
    }

    /** Penambahan tetap harus jalan — itu seluruh gunanya. */
    public function test_new_entries_are_still_recorded(): void
    {
        AuditLog::record('post.published', null, 'Artikel diterbitkan');

        $this->assertDatabaseHas('audit_logs', ['action' => 'post.published']);
    }
}
