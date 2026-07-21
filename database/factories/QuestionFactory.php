<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'question' => $this->faker->sentence(),
            'type' => 'single_choice',
            'position' => $this->faker->unique()->numberBetween(0, 100),
            'points' => 1,
        ];
    }
}
