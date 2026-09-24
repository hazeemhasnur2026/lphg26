<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => null,
            'subject' => rtrim($this->faker->sentence(4), '.'),
            'message' => $this->faker->paragraph(),
            'locale' => 'id',
            'ip_address' => $this->faker->ipv4(),
            'handled_at' => null,
        ];
    }

    public function handled(): static
    {
        return $this->state(fn () => ['handled_at' => now()]);
    }
}
