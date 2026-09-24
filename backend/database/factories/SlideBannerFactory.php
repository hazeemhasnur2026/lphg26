<?php

namespace Database\Factories;

use App\Models\SlideBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlideBanner>
 */
class SlideBannerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => '/assets/img/gallery/01-hasnur-group-she-activity.jpg',
            'image_alt' => $this->faker->sentence(4),
            'title' => $this->faker->words(2, true),
            'subtitle' => $this->faker->sentence(8),
            'order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
