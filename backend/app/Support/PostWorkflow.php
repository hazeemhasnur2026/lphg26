<?php

namespace App\Support;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Posts\PostResource;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\User;
// Filament\Actions\Action, bukan Filament\Notifications\Actions\Action:
// namespace kedua itu milik Filament 3 dan sudah tidak ada di 4. Kekeliruan
// seperti ini lolos dari php -l — kelas baru dicari saat dijalankan — dan baru
// terlihat saat seseorang menekan tombol ajukan.
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Perpindahan status artikel, di satu tempat.
 *
 * Aturannya bisa saja ditulis langsung di tiap tombol Filament, dan itu yang
 * biasanya terjadi. Akibatnya aturan yang sama ditulis tiga kali — di tombol,
 * di tes, dan di pemeriksaan hak — lalu ketiganya berselisih diam-diam saat
 * salah satunya diubah. Kelas ini menjadi satu-satunya yang boleh memindahkan
 * status, sehingga jejak audit tidak bisa terlewat: tidak ada jalan lain.
 */
class PostWorkflow
{
    /** Apakah pengguna ini boleh memindahkan artikel ke status tersebut. */
    public static function allows(User $user, Post $post, PostStatus $target): bool
    {
        $current = $post->status ?? PostStatus::Draft;

        if (! in_array($target, $current->nextStates(), true)) {
            return false;
        }

        return match ($target) {
            // Mengajukan naskah: siapa pun yang boleh menyuntingnya.
            PostStatus::InReview => static::canEdit($user, $post),

            // Menyetujui, menerbitkan, mengarsipkan: hanya yang berwenang.
            PostStatus::Approved, PostStatus::Published, PostStatus::Archived => $user->canApproveContent(),

            // Menarik kembali ke draf. Kontributor boleh menarik naskahnya
            // sendiri selama belum terbit; sesudah terbit, menariknya adalah
            // keputusan redaksi.
            PostStatus::Draft => $current === PostStatus::Published
                ? $user->canPublishContent()
                : static::canEdit($user, $post),
        };
    }

    /**
     * Apakah pengguna ini boleh menyunting isi artikel.
     *
     * Kontributor hanya memegang naskahnya sendiri, dan berhenti memegangnya
     * begitu diajukan: naskah yang sedang ditinjau tidak boleh berubah di bawah
     * tangan peninjaunya.
     */
    public static function canEdit(User $user, Post $post): bool
    {
        if ($user->canEditOthersContent()) {
            return true;
        }

        return $post->author_id === $user->getKey()
            && in_array($post->status, [PostStatus::Draft, null], true);
    }

    /**
     * Memindahkan status, mencatat siapa dan kapan.
     *
     * Tanggal terbit ikut diisi saat menerbitkan hanya bila masih kosong.
     * Menimpanya akan menghapus jadwal yang sudah disetel orang, dan mengubah
     * tanggal artikel lama setiap kali ia diterbitkan ulang setelah perbaikan.
     */
    public static function transition(Post $post, PostStatus $target, ?string $note = null): Post
    {
        $from = $post->status ?? PostStatus::Draft;
        $user = auth()->user();

        $perubahan = ['status' => $target];

        match ($target) {
            PostStatus::InReview => $perubahan += [
                'submitted_at' => now(),
                'submitted_by' => $user?->getKey(),
                'review_note' => null,
            ],
            PostStatus::Approved => $perubahan += [
                'reviewed_at' => now(),
                'reviewed_by' => $user?->getKey(),
                'review_note' => $note,
            ],
            PostStatus::Published => $perubahan += [
                'published_at' => $post->published_at ?? now(),
                'archived_at' => null,
            ],
            PostStatus::Archived => $perubahan += ['archived_at' => now()],
            PostStatus::Draft => $perubahan += ['review_note' => $note],
        };

        $post->forceFill($perubahan)->save();

        AuditLog::record(
            action: 'post.status',
            subject: $post,
            description: sprintf('%s: %s → %s', $post->title, $from->getLabel(), $target->getLabel()),
            changes: array_filter([
                'dari' => $from->value,
                'ke' => $target->value,
                'catatan' => $note,
            ]),
        );

        static::beritahu($post, $target, $note);

        return $post;
    }

    /**
     * Memberi tahu orang yang sekarang gilirannya bertindak.
     *
     * Sebelum ini alurnya bergantung pada orang membuka panel dan melihat angka
     * di menu. Itu cukup bagi yang memang membukanya tiap pagi, dan naskah yang
     * diajukan Jumat sore oleh orang yang tidak membukanya akan menunggu sampai
     * ada yang bertanya.
     *
     * Yang diberi tahu hanya yang punya langkah berikutnya, bukan semua orang.
     * Pemberitahuan yang dikirim ke semua orang berhenti dibaca siapa pun.
     */
    private static function beritahu(Post $post, PostStatus $target, ?string $note): void
    {
        $pelaku = auth()->user();

        [$penerima, $judul, $isi] = match ($target) {
            // Naskah masuk antrean: yang bisa menyetujui perlu tahu.
            PostStatus::InReview => [
                User::whereIn('role', [UserRole::SuperAdmin, UserRole::Admin, UserRole::Reviewer])->get(),
                'Naskah menunggu tinjauan',
                sprintf('%s mengajukan "%s".', $pelaku?->name ?? 'Seseorang', $post->title),
            ],

            // Ditolak: penulisnya perlu tahu apa yang harus diperbaiki, dan
            // catatan peninjau ikut dibawa supaya ia tidak perlu bertanya.
            PostStatus::Draft => [
                static::penulis($post),
                'Naskah dikembalikan ke draf',
                trim(sprintf('"%s" dikembalikan%s', $post->title, $note ? ': '.$note : '.')),
            ],

            PostStatus::Approved => [
                static::penulis($post),
                'Naskah disetujui',
                sprintf('"%s" lolos tinjauan dan siap diterbitkan.', $post->title),
            ],

            PostStatus::Published => [
                static::penulis($post),
                'Naskah terbit',
                sprintf('"%s" sudah tayang di situs.', $post->title),
            ],

            // Pengarsipan tidak memberi tahu siapa pun: itu keputusan redaksi
            // atas konten yang sudah lama tayang, bukan giliran orang lain.
            PostStatus::Archived => [collect(), null, null],
        };

        // Pelakunya sendiri tidak diberi tahu tentang perbuatannya sendiri.
        $penerima = $penerima->reject(fn (User $u) => $u->is($pelaku));

        if ($penerima->isEmpty() || blank($judul)) {
            return;
        }

        $pesan = Notification::make()
            ->title($judul)
            ->body($isi)
            ->icon($target === PostStatus::Draft ? 'heroicon-o-arrow-uturn-left' : 'heroicon-o-document-text')
            ->color($target === PostStatus::Draft ? 'warning' : 'success')
            ->actions([
                Action::make('buka')
                    ->label('Buka naskah')
                    ->url(PostResource::getUrl('edit', ['record' => $post]))
                    ->markAsRead(),
            ])
            ->toDatabase();

        // notifyNow, bukan ->sendToDatabase().
        //
        // Cara itu memanggil notify(), dan Filament\Notifications\DatabaseNotification
        // mengimplementasikan ShouldQueue: pemberitahuannya masuk tabel jobs dan
        // menunggu pekerja antrean. Aplikasi ini tidak menjalankan queue:work di
        // mana pun — container penjadwal memegang schedule:work — sehingga
        // seluruh pemberitahuan mengendap tanpa pernah sampai, tanpa galat, dan
        // tanpa ada yang menyadarinya sampai seseorang bertanya kenapa naskahnya
        // tidak ditinjau-tinjau.
        //
        // Menjalankan pekerja antrean demi satu baris INSERT berarti satu proses
        // panjang lagi yang harus dijaga, dan kontensi kunci tambahan pada basis
        // data SQLite yang sama. Isi pekerjaannya tidak sebanding dengan itu.
        $penerima->each(fn (User $u) => $u->notifyNow($pesan));
    }

    /** Penulis naskah, atau yang mengajukannya bila penulisnya belum diisi. */
    private static function penulis(Post $post): Collection
    {
        return User::whereIn('id', array_filter([$post->author_id, $post->submitted_by]))
            ->get();
    }
}
