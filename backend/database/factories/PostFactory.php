<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Enums\PostStatus;
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
            // Terbit secara bawaan, karena itu keadaan yang dipakai hampir
            // setiap tes: yang diperiksa adalah artikel yang tampil di situs.
            // Tes tentang alur redaksi menyatakan statusnya sendiri.
            'status' => PostStatus::Published,
            'content_type' => ContentType::News,
            // Bergambar secara bawaan: artikel terbit wajib punya sampul, jadi
            // artikel tanpa gambar bukan keadaan bawaan melainkan pengecualian.
            // Tes yang menguji artikel tanpa gambar menyatakannya sendiri.
            'cover_image' => 'unggahan/contoh-sampul.jpg',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => PostStatus::Draft]);
    }

    /** Artikel tanpa gambar sampul — tidak boleh terbit. */
    public function tanpaGambar(): static
    {
        return $this->state(fn () => ['cover_image' => null]);
    }

    public function awaitingReview(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::InReview,
            'submitted_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Archived,
            'archived_at' => now(),
        ]);
    }

    /** Sudah disetujui, tanggal terbitnya belum tiba. */
    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Published,
            'published_at' => now()->addWeek(),
        ]);
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
