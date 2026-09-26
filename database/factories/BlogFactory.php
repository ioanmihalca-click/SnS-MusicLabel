<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'published_at' => now()->subDay(),
            'meta_title' => fake()->text(60),
            'meta_description' => fake()->text(160),
            'meta_keywords' => implode(', ', fake()->words(5)),
            'cover_image' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['published_at' => now()->subDay()]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => ['published_at' => now()->addWeek()]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    /**
     * Announced in the homepage hero for another week (once published).
     */
    public function inHero(?string $heroText = null): static
    {
        return $this->state(fn () => ['hero_until' => now()->addWeek(), 'hero_text' => $heroText]);
    }
}
