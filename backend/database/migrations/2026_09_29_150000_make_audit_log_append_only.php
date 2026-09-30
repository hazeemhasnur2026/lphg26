<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Jejak audit hanya boleh bertambah, dijaga basis data.
 *
 * Lapisan aplikasi sudah menolak sunting dan hapus lewat AuditLogResource. Yang
 * kurang adalah jaminan di bawahnya: satu `AuditLog::find(3)->delete()` di
 * tinker, satu perintah artisan yang ditulis tergesa, atau satu rute baru yang
 * lupa memeriksa — semuanya melewati penjagaan itu tanpa hambatan.
 *
 * Jejak audit yang bisa dirapikan oleh orang yang paling berkepentingan
 * merapikannya tidak menjawab apa pun pada saat ia justru dibutuhkan.
 *
 * Trigger, bukan pencabutan hak: SQLite tidak punya sistem hak akses, dan
 * aplikasi ini memakai satu berkas basis data tanpa pengguna basis data sama
 * sekali. Pada MySQL jalan yang benar adalah mencabut UPDATE dan DELETE dari
 * akun aplikasi, dan migrasi ini melewati dirinya sendiri di sana.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        /*
         * Satu pengecualian, dan hanya satu: user_id yang menjadi NULL.
         *
         * Menghapus akun panel memicu foreign key nullOnDelete, yang berarti
         * satu UPDATE pada tabel ini. Tanpa pengecualian, trigger membatalkan
         * UPDATE itu dan penghapusan akun gagal seluruhnya — jejak audit yang
         * dijaga terlalu ketat berubah menjadi akun yang tidak bisa dihapus.
         *
         * Kondisinya menuntut setiap kolom lain tidak berubah, jadi jalur ini
         * tidak bisa dipakai untuk menyelundupkan perubahan lain. IS dipakai,
         * bukan =, karena = pada NULL selalu bernilai NULL di SQL.
         *
         * Nama pelaku tetap tersimpan di user_name, jadi jejaknya tetap
         * menjawab "siapa" setelah akunnya hilang.
         */
        DB::statement('
            CREATE TRIGGER audit_logs_tolak_ubah
            BEFORE UPDATE ON audit_logs
            WHEN NOT (
                NEW.user_id IS NULL
                AND OLD.user_id IS NOT NULL
                AND NEW.action         IS OLD.action
                AND NEW.description    IS OLD.description
                AND NEW.changes        IS OLD.changes
                AND NEW.user_name      IS OLD.user_name
                AND NEW.ip_address     IS OLD.ip_address
                AND NEW.auditable_type IS OLD.auditable_type
                AND NEW.auditable_id   IS OLD.auditable_id
                AND NEW.created_at     IS OLD.created_at
            )
            BEGIN
                SELECT RAISE(ABORT, "Jejak audit tidak dapat diubah.");
            END
        ');

        DB::statement('
            CREATE TRIGGER audit_logs_tolak_hapus
            BEFORE DELETE ON audit_logs
            BEGIN
                SELECT RAISE(ABORT, "Jejak audit tidak dapat dihapus.");
            END
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS audit_logs_tolak_ubah');
        DB::statement('DROP TRIGGER IF EXISTS audit_logs_tolak_hapus');
    }
};
