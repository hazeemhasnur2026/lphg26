<?php

namespace Database\Factories;

use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
{
    public function definition(): array
    {
        $title = ucwords($this->faker->unique()->words(3, true));

        return [
            'title' => $title,
            'slug' => str($title)->slug()->value(),
            'department' => 'Operasional',
            'location' => 'Banjarmasin, Kalimantan Selatan',
            'description' => $this->faker->paragraph(),
            'requirements' => $this->faker->paragraph(),
            // Situs lama tidak pernah mencantumkan tenggat, jadi default-nya kosong.
            'deadline' => null,
            'is_open' => true,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['is_open' => false]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['deadline' => now()->subDay()]);
    }
}
