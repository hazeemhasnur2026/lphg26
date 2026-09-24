<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'address' => $this->faker->streetAddress()."\n".$this->faker->city(),
            'region' => Office::REPRESENTATIVE,
            'order' => 0,
        ];
    }

    public function headquarters(): static
    {
        return $this->state(fn () => [
            'region' => Office::HEADQUARTERS,
            'phone' => '(+6221) 29343888',
            'fax' => '(+6221) 29343777',
            'email' => 'info@hasnurgroup.com',
        ]);
    }
}
