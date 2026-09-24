<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

/**
 * Tiga kantor, disalin dari footer yang sebelumnya ditulis tangan.
 *
 * Alamat disimpan dengan baris baru sesuai bentuk aslinya di footer, lalu
 * ditampilkan dengan <br>, supaya orang yang menyuntingnya dari admin tidak
 * perlu menulis tag HTML.
 */
class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            [
                'name' => 'Kantor Pusat — Jakarta',
                'address' => "Office 8 Building, Lantai 7\nJl. Senopati No. 8B, Jakarta Selatan\nDKI Jakarta 12190",
                'phone' => '(+6221) 29343888',
                'fax' => '(+6221) 29343777',
                'email' => 'info@hasnurgroup.com',
                'region' => Office::HEADQUARTERS,
                'order' => 0,
            ],
            [
                'name' => 'Kalimantan Selatan',
                'address' => 'Jl. Berangas Timur No. 95 A, Alalak, Kalimantan Selatan 70582',
                'region' => Office::REPRESENTATIVE,
                'order' => 1,
            ],
            [
                'name' => 'Kalimantan Tengah',
                'address' => 'Jl. G. Obos No. 88 A, Palangkaraya, Kalimantan Tengah 73112',
                'region' => Office::REPRESENTATIVE,
                'order' => 2,
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(['name' => $office['name']], $office);
        }
    }
}
