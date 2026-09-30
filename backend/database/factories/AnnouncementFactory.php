<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => rtrim($this->faker->sentence(5), '.'),
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            // Aktif secara bawaan: hampir setiap tes di sini memeriksa yang
            // tampil. Yang menguji keadaan tidak aktif menyatakannya sendiri.
            'is_active' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function terjadwal(): static
    {
        return $this->state(fn () => ['starts_at' => now()->addWeek()]);
    }

    public function kedaluwarsa(): static
    {
        return $this->state(fn () => ['ends_at' => now()->subDay()]);
    }
}
