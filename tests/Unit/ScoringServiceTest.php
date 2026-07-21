<?php

namespace Tests\Unit;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_scoring_uses_question_weights_and_passing_percentage(): void
    {
        $quiz = Quiz::create(['title' => 'Weighted', 'slug' => 'weighted', 'description' => 'd', 'type' => 'quiz', 'passing_score' => 70]);
        $first = Question::create(['quiz_id' => $quiz->id, 'question' => 'One', 'position' => 1, 'points' => 3]);
        $second = Question::create(['quiz_id' => $quiz->id, 'question' => 'Two', 'position' => 2, 'points' => 7]);
        $firstCorrect = Option::create(['question_id' => $first->id, 'label' => 'Correct', 'is_correct' => true, 'position' => 1]);
        $secondWrong = Option::create(['question_id' => $second->id, 'label' => 'Wrong', 'is_correct' => false, 'position' => 1]);

        $result = app(ScoringService::class)->calculate($quiz->fresh('questions.options'), collect([$first->id => $firstCorrect, $second->id => $secondWrong]));

        $this->assertSame(3, $result['score']);
        $this->assertSame(10, $result['max_score']);
        $this->assertSame(30, $result['percentage']);
        $this->assertSame('Belum lulus', $result['summary']['message']);
    }
}
