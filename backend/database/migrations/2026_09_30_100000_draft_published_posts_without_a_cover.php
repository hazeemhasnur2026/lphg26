<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Artikel terbit tanpa gambar sampul diturunkan menjadi draf.
 *
 * Aturan redaksinya baru: artikel yang tampil di situs wajib bergambar. Gerbang
 * terbit sudah menutup jalan bagi artikel baru, dan importer sudah menurunkan
 * yang datang tanpa gambar — yang tersisa adalah artikel yang sudah terlanjur
 * tayang sebelum aturan ini ada.
 *
 * Diturunkan, bukan dihapus, dan bukan pula dibiarkan. Isinya tetap utuh di
 * panel, muncul di penyaring Draf, dan bisa diterbitkan lagi begitu gambarnya
 * dipasang. Yang berubah hanya: ia berhenti tampil di situs.
 *
 * Tiap penurunan dicatat di jejak audit. Konten yang hilang dari situs tanpa
 * ada yang bisa menjelaskan kenapa adalah cara tercepat membuat orang tidak
 * percaya pada panelnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        $terdampak = DB::table('posts')
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('cover_image')->orWhere('cover_image', ''))
            ->get(['id', 'title']);

        if ($terdampak->isEmpty()) {
            return;
        }

        DB::table('posts')
            ->whereIn('id', $terdampak->pluck('id'))
            ->update(['status' => 'draft']);

        // Ditulis langsung lewat query builder, bukan lewat AuditLog::record():
        // model itu memanggil auth() dan request(), dan keduanya tidak berarti
        // apa-apa saat migrasi dijalankan dari baris perintah.
        $sekarang = now();

        DB::table('audit_logs')->insert(
            $terdampak->map(fn ($p) => [
                'auditable_type' => Post::class,
                'auditable_id' => $p->id,
                'action' => 'post.status',
                'description' => sprintf(
                    '%s: Terbit → Draf (aturan baru: artikel terbit wajib bergambar)',
                    $p->title,
                ),
                'changes' => json_encode([
                    'dari' => 'published',
                    'ke' => 'draft',
                    'alasan' => 'tanpa gambar sampul',
                ]),
                'user_id' => null,
                'user_name' => 'sistem (migrasi)',
                'ip_address' => null,
                'created_at' => $sekarang,
            ])->all()
        );
    }

    /**
     * Tidak dikembalikan.
     *
     * Menaikkan ulang artikel tanpa gambar berarti melanggar aturan yang masih
     * berlaku, dan migrasi ini tidak menyimpan mana yang tadinya terbit karena
     * aturan ini dan mana yang memang draf sejak awal. Yang bisa dikembalikan
     * dikerjakan orang lewat panel, satu per satu, setelah gambarnya dipasang.
     */
    public function down(): void
    {
        //
    }
};
