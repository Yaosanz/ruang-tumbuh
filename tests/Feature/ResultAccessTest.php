<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_uses_unpredictable_public_id_and_rejects_numeric_id(): void
    {
        $quiz = Quiz::create(['title' => 'Result Quiz', 'slug' => 'result-quiz', 'description' => 'd', 'type' => 'quiz', 'passing_score' => 70]);
        $submission = Submission::create(['quiz_id' => $quiz->id, 'participant_name' => 'Sandy', 'score' => 7, 'max_score' => 10, 'percentage' => 70, 'completed_at' => now()]);

        $this->get(route('results.show', $submission))->assertOk()->assertSee('Sandy')->assertSee('Lulus');
        $this->get('/result/'.$submission->id)->assertNotFound();
    }
}
