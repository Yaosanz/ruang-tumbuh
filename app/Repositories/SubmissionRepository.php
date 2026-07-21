<?php

namespace App\Repositories;

use App\Models\Submission;

class SubmissionRepository
{
    public function create(array $attributes): Submission
    {
        return Submission::create($attributes);
    }

    public function saveAnswer(Submission $submission, int $questionId, int $optionId, int $value): void
    {
        $submission->answers()->create(['question_id' => $questionId, 'option_id' => $optionId, 'value' => $value]);
    }
}
