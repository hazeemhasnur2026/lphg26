<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim($this->faker->sentence(6), '.');

        return [
            'title' => $title,
            'slug' => str($title)->slug()->value(),
            'category' => 'latest-update',
            'excerpt' => $this->faker->sentence(12),
            // Kolomnya NOT NULL: kutipan liputan media menyimpan string kosong,
            // bukan null, karena isinya memang bukan milik kami.
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            'published_at' => $this->faker->dateTimeBetween('-2 years'),
        ];
    }

    /** Artikel yang datelinenya belum dikonfirmasi manusia. */
    public function undated(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    /** Liputan media: kutipan, bukan salinan isinya. */
    public function citation(string $outlet = 'ANTARA'): static
    {
        return $this->state(fn () => [
            'body' => '',
            'category' => 'news',
            'source_name' => $outlet,
            'external_url' => 'https://www.antaranews.com/berita/contoh',
        ]);
    }
}
