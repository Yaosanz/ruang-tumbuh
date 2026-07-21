<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['quiz', 'assessment']),
            'duration_minutes' => $this->faker->optional(0.7)->numberBetween(5, 60),
            'passing_score' => 70,
            'is_published' => $this->faker->boolean(70),
            'interpretation_ranges' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => ['is_published' => true]);
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => ['is_published' => false]);
    }

    public function assessment(): static
    {
        return $this->state(fn(array $attributes) => ['type' => 'assessment']);
    }

    public function quiz(): static
    {
        return $this->state(fn(array $attributes) => ['type' => 'quiz']);
    }
}
