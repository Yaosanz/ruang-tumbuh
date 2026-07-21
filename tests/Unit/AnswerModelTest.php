<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Option;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_answer_belongs_to_submission(): void
    {
        $submission = Submission::factory()->create();
        $answer = Answer::factory()->create(['submission_id' => $submission->id]);

        $this->assertTrue($answer->submission()->exists());
        $this->assertEquals($submission->id, $answer->submission->id);
    }

    public function test_answer_belongs_to_question(): void
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $this->assertTrue($answer->question()->exists());
        $this->assertEquals($question->id, $answer->question->id);
    }

    public function test_answer_belongs_to_option(): void
    {
        $option = Option::factory()->create();
        $answer = Answer::factory()->create(['option_id' => $option->id]);

        $this->assertTrue($answer->option()->exists());
        $this->assertEquals($option->id, $answer->option->id);
    }

    public function test_answer_can_have_null_option(): void
    {
        $answer = Answer::factory()->create(['option_id' => null]);

        $this->assertNull($answer->option_id);
        $this->assertNull($answer->option);
    }
}
