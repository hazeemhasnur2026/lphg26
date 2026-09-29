<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\AuditLog;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => auth()->user()?->canEditOthersContent() ?? false)
                // Penghapusan ikut membawa seluruh revisinya, jadi jejaknya
                // dicatat sebelum barangnya hilang.
                ->before(fn (Post $record) => AuditLog::record(
                    action: 'post.deleted',
                    subject: $record,
                    description: 'Artikel dihapus: '.$record->title,
                )),
        ];
    }

    /**
     * Versi disimpan sebelum perubahan ditulis.
     *
     * Yang tersimpan adalah isi yang sedang ditinggalkan, bukan isi yang baru.
     * Itu yang dicari orang saat ingin kembali: "kembalikan ke sebelum saya
     * mengubahnya", bukan "kembalikan ke yang barusan saya simpan".
     *
     * Revisi hanya dibuat bila ada kolom naskah yang benar-benar berubah.
     * Tanpa penyaringan ini, membuka artikel lalu menekan Simpan tanpa mengubah
     * apa pun akan menambah satu versi, dan riwayat penuh versi kembar membuat
     * yang asli tidak bisa ditemukan.
     */
    /**
     * Nilai dalam bentuk yang bisa dibandingkan.
     *
     * content_type datang sebagai string dari formulir dan sebagai enum dari
     * model, dan PHP menyatakan keduanya berbeda: 'news' != ContentType::News
     * bernilai true. Dibandingkan mentah-mentah, tiap penyimpanan akan terlihat
     * mengubah tipe kontennya, dan membuka artikel lalu menekan Simpan tanpa
     * menyentuh apa pun akan melahirkan satu versi baru. Riwayat yang penuh
     * versi kembar membuat versi yang sungguh berbeda tidak bisa ditemukan.
     */
    private static function skalar(mixed $nilai): mixed
    {
        return $nilai instanceof \BackedEnum ? $nilai->value : $nilai;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        $berubah = collect(Post::REVISIONED_FIELDS)
            ->filter(fn (string $field) => array_key_exists($field, $data)
                && static::skalar($data[$field]) !== static::skalar($record->getAttribute($field)))
            ->values();

        if ($berubah->isNotEmpty()) {
            $record->recordRevision('Sebelum suntingan '.now()->format('d M Y H:i'));

            AuditLog::record(
                action: 'post.updated',
                subject: $record,
                description: 'Artikel disunting: '.$record->title,
                changes: ['kolom' => $berubah->all()],
            );
        }

        return $data;
    }
}
