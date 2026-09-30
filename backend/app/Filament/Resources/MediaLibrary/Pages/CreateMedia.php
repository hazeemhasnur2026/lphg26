<?php

namespace App\Filament\Resources\MediaLibrary\Pages;

use App\Filament\Resources\MediaLibrary\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    /**
     * Tipe dan ukuran dibaca dari berkas yang sudah tersimpan, bukan dari
     * unggahannya.
     *
     * Bedanya penting saat berkas diproses ulang setelah diunggah: yang dicatat
     * harus berkas yang benar-benar akan dilayani ke pengunjung.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $disk = Storage::disk('content');
        $path = $data['path'];

        $data['disk'] = 'content';
        $data['uploaded_by'] = auth()->id();
        $data['size'] = $disk->exists($path) ? $disk->size($path) : 0;
        $data['mime_type'] = ($disk->exists($path) ? $disk->mimeType($path) : null)
            ?: 'application/octet-stream';
        $data['original_name'] = $data['original_name'] ?: basename($path);

        return $data;
    }
}
