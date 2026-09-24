<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],

            /*
             * Perangkap robot: medan ini disembunyikan dari mata manusia lewat
             * CSS dan aria-hidden, jadi manusia tidak akan pernah mengisinya.
             * Pengisi otomatis yang membaca HTML apa adanya akan mengisinya, dan
             * kiriman itu ditolak sebagai gagal validasi biasa. Tanpa memberi
             * tahu robotnya apa yang salah.
             */
            'website' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'alamat surel',
            'phone' => 'nomor telepon',
            'subject' => 'perihal',
            'message' => 'pesan',
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'Mohon tuliskan pesan yang lebih lengkap, minimal :min karakter.',
            'website.prohibited' => 'Kiriman tidak dapat diproses.',
        ];
    }
}
