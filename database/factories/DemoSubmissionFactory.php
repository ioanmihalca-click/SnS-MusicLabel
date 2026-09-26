<?php

namespace Database\Factories;

use App\Enums\DemoStatus;
use App\Enums\Genre;
use App\Models\DemoSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoSubmission>
 */
class DemoSubmissionFactory extends Factory
{
    protected $model = DemoSubmission::class;

    public function definition(): array
    {
        return [
            'artist_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'link' => 'https://soundcloud.com/'.fake()->userName().'/'.fake()->slug(3).'/s-'.fake()->regexify('[A-Za-z0-9]{11}'),
            'genre' => fake()->randomElement(Genre::cases())->value,
            'country' => fake()->country(),
            'message' => fake()->paragraph(),
            'rights_confirmed' => true,
            'status' => DemoStatus::New,
            'notes' => null,
        ];
    }

    public function status(DemoStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function accepted(): static
    {
        return $this->status(DemoStatus::Accepted);
    }

    public function declined(): static
    {
        return $this->status(DemoStatus::Declined);
    }
}
