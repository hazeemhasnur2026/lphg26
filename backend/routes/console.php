<?php

use App\Models\AnalyticsEvent;
use App\Models\ContactMessage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Pemangkasan pesan dan peristiwa analitik yang sudah melewati masa simpan.
 *
 * Butuh satu baris cron di server, kalau tidak jadwal ini tidak pernah jalan:
 *   * * * * * cd /path/ke/backend && php artisan schedule:run >> /dev/null 2>&1
 *
 * Untuk melihat apa yang akan terhapus tanpa menghapusnya:
 *   php artisan model:prune --pretend
 *
 * Sesi kedaluwarsa tidak dijadwalkan di sini karena driver database Laravel
 * sudah membersihkannya sendiri lewat session.lottery.
 */
Schedule::command('model:prune', ['--model' => [ContactMessage::class, AnalyticsEvent::class]])
    ->daily()
    ->onOneServer();

/*
 * Pemangkasan riwayat versi.
 *
 * Tiap revisi menyimpan satu salinan penuh badan artikel. Tanpa batas, tabelnya
 * tumbuh selamanya dan tidak ada yang melihatnya sampai backup mulai terasa
 * berat. Batasnya di config/site.php, diisi 0 untuk mematikannya.
 */
Schedule::command('revisions:prune')
    ->weekly()
    ->onOneServer();

/*
 * Backup harian: basis data dan gambar yang diunggah lewat panel admin.
 *
 * Skripnya sudah lama ada di deploy/backup.sh, tapi sampai sekarang menunggu
 * baris cron yang dipasang tangan di server. Di dalam Docker baris itu tidak
 * pernah ada, sehingga satu-satunya dua hal yang tidak bisa dibangun ulang dari
 * repositori justru tidak pernah tercadangkan.
 *
 * Hanya container penjadwal yang menjalankan `schedule:work`, jadi tidak ada
 * dua proses yang berebut; ->onOneServer() menjaganya tetap begitu bila kelak
 * ada lebih dari satu.
 *
 * Keluarannya ditulis ke berkas, bukan dibuang. Backup yang gagal diam-diam
 * sama saja dengan tidak punya backup, dan baru ketahuan saat dibutuhkan.
 */
Schedule::exec(dirname(base_path()).'/deploy/backup.sh')
    ->dailyAt('02:15')
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
