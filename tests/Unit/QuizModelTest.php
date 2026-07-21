<?php

namespace Tests\Unit;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_has_many_questions_ordered_by_position(): void
    {
        $quiz = Quiz::factory()->create();
        $q2 = Question::factory()->create(['quiz_id' => $quiz->id, 'position' => 2]);
        $q1 = Question::factory()->create(['quiz_id' => $quiz->id, 'position' => 1]);
        $q0 = Question::factory()->create(['quiz_id' => $quiz->id, 'position' => 0]);

        $questions = $quiz->questions;
        $this->assertCount(3, $questions);
        $this->assertEquals($q0->id, $questions[0]->id);
        $this->assertEquals($q1->id, $questions[1]->id);
        $this->assertEquals($q2->id, $questions[2]->id);
    }

    public function test_quiz_has_many_submissions(): void
    {
        $quiz = Quiz::factory()->create();
        Submission::factory()->count(3)->create(['quiz_id' => $quiz->id]);

        $this->assertCount(3, $quiz->submissions);
    }

    public function test_quiz_route_key_is_slug(): void
    {
        $quiz = Quiz::factory()->create(['slug' => 'test-quiz-slug']);

        $this->assertEquals('test-quiz-slug', $quiz->getRouteKey());
        $this->assertEquals('slug', $quiz->getRouteKeyName());
    }

    public function test_quiz_casts_is_published_as_boolean(): void
    {
        $quiz = Quiz::factory()->create(['is_published' => true]);

        $this->assertTrue($quiz->is_published);
        $this->assertIsBool($quiz->is_published);
    }

    public function test_quiz_casts_interpretation_ranges_as_array(): void
    {
        $ranges = [['min' => 0, 'max' => 5, 'label' => 'Low']];
        $quiz = Quiz::factory()->create(['interpretation_ranges' => $ranges]);

        $this->assertIsArray($quiz->interpretation_ranges);
        $this->assertEquals($ranges, $quiz->interpretation_ranges);
    }

    public function test_scoring_for_assessment_type_uses_option_values(): void
    {
        $quiz = Quiz::factory()->create(['type' => 'assessment']);
        $question = Question::factory()->create(['quiz_id' => $quiz->id, 'points' => 1]);
        Option::factory()->create(['question_id' => $question->id, 'value' => 1, 'is_correct' => false]);
        Option::factory()->create(['question_id' => $question->id, 'value' => 2, 'is_correct' => false]);
        Option::factory()->create(['question_id' => $question->id, 'value' => 3, 'is_correct' => true]);

        $quiz->load('questions.options');
        $maxValue = $quiz->questions->sum(fn($q) => $q->options->max('value'));
        $this->assertEquals(3, $maxValue);
    }

    public function test_scoring_for_quiz_type_uses_points(): void
    {
        $quiz = Quiz::factory()->create(['type' => 'quiz']);
        Question::factory()->create(['quiz_id' => $quiz->id, 'points' => 5]);
        Question::factory()->create(['quiz_id' => $quiz->id, 'points' => 10]);

        $quiz->load('questions');
        $totalPoints = $quiz->questions->sum('points');
        $this->assertEquals(15, $totalPoints);
    }
}
