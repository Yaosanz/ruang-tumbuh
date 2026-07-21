<?php

namespace Tests\Unit;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_rejects_an_option_from_another_question_without_creating_submission(): void
    {
        $quiz = Quiz::create(['title' => 'Secure', 'slug' => 'secure', 'description' => 'd', 'type' => 'quiz']);
        $first = Question::create(['quiz_id' => $quiz->id, 'question' => 'First', 'position' => 1]);
        $second = Question::create(['quiz_id' => $quiz->id, 'question' => 'Second', 'position' => 2]);
        $foreignOption = Option::create(['question_id' => $second->id, 'label' => 'Foreign', 'position' => 1]);

        $this->expectException(ValidationException::class);
        try {
            app(SubmissionService::class)->submit($quiz->fresh('questions.options'), [$first->id => $foreignOption->id, $second->id => $foreignOption->id], null, null);
        } finally {
            $this->assertSame(0, Submission::count());
        }
    }
}
