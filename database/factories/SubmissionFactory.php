<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'participant_name' => $this->faker->name(),
            'participant_email' => $this->faker->optional(0.7)->email(),
            'score' => $this->faker->numberBetween(0, 100),
            'max_score' => 100,
            'percentage' => $this->faker->numberBetween(0, 100),
            'completed_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
