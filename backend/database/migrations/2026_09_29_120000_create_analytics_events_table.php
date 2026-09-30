<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peristiwa analitik milik situs sendiri (CMS-10).
 *
 * TOR meminta empat peristiwa — CTA, unduhan, pencarian, keterlibatan isi —
 * dan integrasi ke "platform analitik yang disetujui". Platformnya belum
 * dipilih, dan sampai dipilih tidak ada satu pun angka yang bisa dilihat
 * siapa pun: skrip GA/GTM menunggu id di .env, dan tanpa id ia tidak menyala.
 *
 * Tabel ini menjawab kekosongan itu. Peristiwa yang sama dikirim dua arah:
 * ke dataLayer untuk platform yang kelak dipilih, dan ke sini untuk dibaca
 * di panel hari ini. Keduanya tidak saling menggantikan — yang di sini tidak
 * punya atribusi kampanye, funnel, atau perbandingan lintas situs — tapi
 * pertanyaan "tombol mana yang diklik orang" tidak perlu menunggu rapat.
 *
 * Yang sengaja tidak disimpan: alamat IP dan User-Agent apa adanya. Keduanya
 * cukup untuk menunjuk satu orang, dan tidak ada pertanyaan di papan analitik
 * yang membutuhkannya. Yang disimpan hanya sidik pendek hasil hash keduanya
 * bersama tanggal (kolom `visitor`), yang berganti tiap tengah malam dan tidak
 * bisa dikembalikan ke asalnya. Gunanya satu: membedakan sepuluh klik dari
 * satu orang dengan sepuluh klik dari sepuluh orang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            // Nama peristiwa, sama persis dengan yang dikirim ke dataLayer.
            // Daftar yang diterima ada di App\Models\AnalyticsEvent; kolomnya
            // tidak dibuat enum supaya menambah peristiwa baru tidak perlu
            // migrasi yang mengubah struktur tabel yang sudah berisi data.
            $table->string('name', 40);

            // Isi peristiwanya: nama CTA, nama berkas, kata kunci pencarian,
            // atau judul artikel. Satu kolom untuk keempatnya, bukan empat
            // kolom yang tiga di antaranya selalu kosong pada tiap baris.
            $table->string('label', 160)->nullable();

            // Halaman tempat peristiwa terjadi. Jalur saja, tanpa host: seluruh
            // baris di sini berasal dari situs ini sendiri.
            $table->string('path', 255)->nullable();

            $table->string('locale', 5)->nullable();
            $table->string('visitor', 32)->nullable();

            // Baris yang dibuat perintah analitik:contoh, bukan oleh pengunjung.
            // Papan analitik yang mencampur data contoh dengan data sungguhan
            // tanpa mengatakannya adalah papan yang menyesatkan justru pada saat
            // ia paling dipercaya; kolom ini yang membuat keduanya bisa
            // dibedakan, dihitung, dan dibuang kembali secara utuh.
            $table->boolean('demo')->default(false);

            $table->timestamp('created_at')->nullable();

            // Tiap kueri papan analitik berbentuk sama: satu nama peristiwa,
            // dalam rentang tanggal tertentu. Indeks gabungan menjawabnya
            // dalam satu penelusuran; dua indeks terpisah memaksa mesin
            // memilih salah satu lalu menyaring sisanya baris per baris.
            $table->index(['name', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
