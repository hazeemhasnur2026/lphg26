<?php

use App\Models\ContactMessage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Pemangkasan pesan yang sudah melewati masa simpan.
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
Schedule::command('model:prune', ['--model' => [ContactMessage::class]])
    ->daily()
    ->onOneServer();
