<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    protected $model = Option::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'label' => $this->faker->word(),
            'value' => $this->faker->numberBetween(1, 5),
            'is_correct' => false,
            'position' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn(array $attributes) => ['is_correct' => true]);
    }
}
