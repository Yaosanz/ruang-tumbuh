<?php

namespace Tests\Unit;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Submission;
use App\Services\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QuizServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_structural_edits_are_blocked_after_a_submission_exists(): void
    {
        $quiz = Quiz::create(['title' => 'Protected', 'slug' => 'protected', 'description' => 'd', 'type' => 'quiz']);
        $question = Question::create(['quiz_id' => $quiz->id, 'question' => 'Original', 'position' => 1, 'points' => 1]);
        $option = Option::create(['question_id' => $question->id, 'label' => 'Yes', 'is_correct' => true, 'position' => 1]);
        $submission = Submission::create(['quiz_id' => $quiz->id, 'participant_name' => 'Guest', 'completed_at' => now()]);
        $submission->answers()->create(['question_id' => $question->id, 'option_id' => $option->id, 'value' => 1]);

        $this->expectException(ValidationException::class);
        app(QuizService::class)->save($quiz, ['title' => 'Protected', 'description' => 'd', 'type' => 'quiz', 'category' => 'knowledge', 'assessment_type' => null, 'duration_minutes' => null, 'passing_score' => 70, 'is_published' => false], [['question' => 'Changed', 'type' => 'single_choice', 'points' => 1, 'options' => ['Yes', 'No'], 'correct' => 0]], null);
    }
}
