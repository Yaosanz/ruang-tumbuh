<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_published_quiz_through_the_api(): void
    {
        $quiz = Quiz::create(['title' => 'API Quiz', 'slug' => 'api-quiz', 'description' => 'd', 'type' => 'assessment', 'is_published' => true]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question' => 'Q1', 'type' => 'scale', 'position' => 1]);
        $option = Option::create(['question_id' => $question->id, 'label' => 'A', 'value' => 2, 'position' => 1]);

        $response = $this->postJson('/api/quizzes/api-quiz/submit', ['answers' => [$question->id => $option->id]]);

        $response->assertCreated()->assertJsonPath('data.score', 2)->assertJsonStructure(['data' => ['id', 'percentage', 'result_summary']]);
        $this->assertDatabaseHas('submissions', ['quiz_id' => $quiz->id, 'score' => 2]);
    }
}
