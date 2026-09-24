<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessUnit>
 */
class BusinessUnitFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => str($name)->slug()->value(),
            'icon' => '/assets/img/bisnis/1.logistics.png',
            'image' => '/assets/img/pages/our-business-logistic/banner.png',
            'image_alt' => 'Foto unit bisnis',
            'tint_color' => '#EBF6FB',
            'accent_color' => '#008CC8',
            'short_description' => $this->faker->sentence(10),
            'order' => 0,
        ];
    }
}
