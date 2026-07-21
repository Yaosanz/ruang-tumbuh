<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Submission;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'question_id' => Question::factory(),
            'option_id' => Option::factory(),
            'value' => $this->faker->numberBetween(1, 5),
        ];
    }
}
