<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Models\AuditLog;
use App\Models\Post;
use App\Models\PostRevision;
use App\Support\PostWorkflow;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Riwayat versi sebuah artikel, dengan tombol kembalikan.
 *
 * Mengembalikan versi tidak menghapus apa pun. Isi yang sedang berlaku
 * disimpan lebih dulu sebagai versi baru, lalu isi lama ditulis di atasnya.
 * Jadi pengembalian yang keliru bisa dibatalkan dengan cara yang sama, dan
 * riwayatnya tetap berupa garis lurus yang bisa dibaca dari atas ke bawah.
 */
class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Riwayat Versi';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Versi')
                    ->sortable(),

                TextColumn::make('summary')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('author.name')
                    ->label('Oleh')
                    ->placeholder('sistem'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                // Nilainya diambil lewat ->state(), bukan ->formatStateUsing().
                //
                // snapshot adalah kolom JSON yang di-cast menjadi array, dan
                // Filament memperlakukan state berupa array sebagai daftar
                // nilai: formatStateUsing dipanggil sekali untuk tiap elemen,
                // bukan sekali untuk arraynya. Yang tampil jadi sederet "—"
                // sepanjang jumlah kolom yang tersimpan.
                //
                // title di dalam snapshot sendiri kini array per-bahasa
                // (['id' => ..., 'en' => ...]) karena Post::recordRevision()
                // menyimpan getTranslations(), bukan string tunggal. Revisi
                // yang dicatat sebelum perubahan itu masih berupa string —
                // keduanya ditangani di sini.
                TextColumn::make('judul_saat_itu')
                    ->label('Judul saat itu')
                    ->state(function (PostRevision $record) {
                        $title = $record->snapshot['title'] ?? null;

                        if (is_array($title)) {
                            return $title['id'] ?? reset($title) ?: '—';
                        }

                        return $title ?: '—';
                    })
                    ->wrap(),
            ])
            // Urutan ditetapkan di sini, bukan pada relasinya: relasi yang
            // membawa urutan sendiri mengunci setiap pemanggil lain.
            ->defaultSort('version', 'desc')
            ->recordActions([
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalDescription('Isi artikel dikembalikan ke versi ini. Isi yang sekarang disimpan dulu sebagai versi baru, jadi langkah ini bisa dibatalkan.')
                    ->visible(fn () => auth()->user()
                        && PostWorkflow::canEdit(auth()->user(), $this->getOwnerRecord()))
                    ->action(function (PostRevision $record) {
                        /** @var Post $post */
                        $post = $this->getOwnerRecord();

                        $post->recordRevision('Sebelum dikembalikan ke versi '.$record->version);

                        // Hanya kolom naskah yang ditulis ulang. Status dan
                        // tanggal terbit tidak ikut: mengembalikan tulisan bukan
                        // alasan untuk membatalkan persetujuan orang lain.
                        $post->forceFill(collect($record->snapshot)
                            ->only(Post::REVISIONED_FIELDS)
                            ->all())->save();

                        AuditLog::record(
                            action: 'post.reverted',
                            subject: $post,
                            description: sprintf('%s dikembalikan ke versi %d', $post->title, $record->version),
                            changes: ['versi' => $record->version],
                        );

                        Notification::make()
                            ->title('Dikembalikan ke versi '.$record->version)
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
