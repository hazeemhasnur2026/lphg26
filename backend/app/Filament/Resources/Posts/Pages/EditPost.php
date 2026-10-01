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

    /**
     * Peta terjemahan dalam bentuk yang bisa dibandingkan.
     *
     * Dua sisi yang dibandingkan memakai cara berbeda untuk menyatakan "belum
     * ada terjemahan", dan tanpa disamakan lebih dulu keduanya tidak pernah
     * cocok:
     *
     * - getTranslations() tidak memuat kunci bahasa yang tidak pernah diisi,
     *   jadi field tanpa versi Inggris kembali sebagai ['id' => ...] saja.
     * - Formulir selalu mengirim kunci untuk tiap tab bahasa. Tab teks biasa
     *   yang dibiarkan kosong datang sebagai null, dan RichEditor yang kosong
     *   datang sebagai "<p></p>" — bukan string kosong.
     *
     * Akibatnya tiap klik Simpan terlihat mengubah excerpt, seo_title, dan
     * meta_description sekaligus, dan riwayat versi terisi versi kembar.
     * Ditemukan lewat pengujian di panel yang sungguhan, bukan lewat pembacaan
     * kode: perbandingannya terlihat benar sampai datanya dilihat.
     */
    private static function petaTerjemahan(array $nilai): array
    {
        $kosong = ['', '<p></p>', '<p><br></p>'];

        $hasil = [];

        foreach ($nilai as $bahasa => $teks) {
            $teks = (string) $teks;

            if (in_array(trim($teks), $kosong, true)) {
                continue;
            }

            $hasil[$bahasa] = $teks;
        }

        ksort($hasil);

        return $hasil;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        /*
         * Field translatable (title, excerpt, body, seo_title,
         * meta_description) dibungkus TranslatableFieldsPlugin jadi tab per
         * bahasa, dan statePath tiap tab jadi "{field}.{locale}" — jadi
         * $data['title'] sekarang array ['id'=>..,'en'=>..], bukan string.
         * Membandingkannya lewat skalar() (yang mengasumsikan string/enum)
         * selalu "berbeda" karena array tidak pernah sama dengan string,
         * sehingga tiap klik Simpan membuat revisi baru walau tak ada yang
         * diubah. Field translatable dibandingkan sebagai peta bahasa;
         * lainnya tetap lewat skalar() seperti sebelumnya.
         */
        $berubah = collect(Post::REVISIONED_FIELDS)
            ->filter(function (string $field) use ($data, $record) {
                if (! array_key_exists($field, $data)) {
                    return false;
                }

                if (in_array($field, $record->translatable, true)) {
                    return static::petaTerjemahan($data[$field] ?? [])
                        !== static::petaTerjemahan($record->getTranslations($field));
                }

                return static::skalar($data[$field]) !== static::skalar($record->getAttribute($field));
            })
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
