<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Models\AuditLog;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    /**
     * Artikel baru selalu lahir sebagai draf.
     *
     * Statusnya tidak diambil dari formulir walaupun kolomnya ada. Kalau boleh
     * dipilih saat membuat, seorang kontributor cukup memilih "Terbit" dan
     * seluruh alur persetujuan menjadi hiasan.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = PostStatus::Draft;
        $data['author_id'] ??= auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        AuditLog::record(
            action: 'post.created',
            subject: $this->getRecord(),
            description: 'Artikel dibuat: '.$this->getRecord()->title,
        );
    }
}
