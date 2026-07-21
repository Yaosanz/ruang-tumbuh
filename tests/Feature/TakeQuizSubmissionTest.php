<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class TakeQuizSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_creates_submission_and_redirects_to_result(): void
    {
        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'desc',
            'type' => 'assessment',
            'duration_minutes' => 5,
            'is_published' => true,
        ]);

        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Q1',
            'type' => 'single_choice',
            'position' => 1,
            'points' => 1,
        ]);

        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Q2',
            'type' => 'single_choice',
            'position' => 2,
            'points' => 1,
        ]);

        $q1Option = Option::create([
            'question_id' => $q1->id,
            'label' => 'Q1-A',
            'value' => 2,
            'is_correct' => false,
            'position' => 1,
        ]);

        $q2Option = Option::create([
            'question_id' => $q2->id,
            'label' => 'Q2-A',
            'value' => 3,
            'is_correct' => false,
            'position' => 1,
        ]);

        $component = Livewire::test('take-quiz', ['quiz' => $quiz])
            ->set('name', 'Sandy')
            ->set('email', 'sandy@example.com')
            ->set('answers', [
                (string) $q1->id => (string) $q1Option->id,
                (string) $q2->id => (string) $q2Option->id,
            ])
            ->call('submit');

        $submission = Submission::query()->latest('id')->first();
        $this->assertNotNull($submission);
        $this->assertNotNull($submission->public_id);
        $component->assertRedirect(route('results.show', $submission));
        $this->assertSame($quiz->id, $submission->quiz_id);
        $this->assertSame(2, $submission->answers()->count('id'));
        $this->assertSame(5, $submission->max_score);
        $this->assertSame(100, $submission->percentage);
    }

    public function test_submit_rejects_option_that_belongs_to_other_question(): void
    {
        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'slug' => 'test-quiz',
            'description' => 'desc',
            'type' => 'assessment',
            'duration_minutes' => 5,
            'is_published' => true,
        ]);

        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Q1',
            'type' => 'single_choice',
            'position' => 1,
            'points' => 1,
        ]);

        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Q2',
            'type' => 'single_choice',
            'position' => 2,
            'points' => 1,
        ]);

        Option::create([
            'question_id' => $q1->id,
            'label' => 'Q1-A',
            'value' => 1,
            'is_correct' => false,
            'position' => 1,
        ]);

        $q2Option = Option::create([
            'question_id' => $q2->id,
            'label' => 'Q2-A',
            'value' => 2,
            'is_correct' => false,
            'position' => 1,
        ]);

        Livewire::test('take-quiz', ['quiz' => $quiz])
            ->set('name', 'Sandy')
            ->set('email', 'sandy@example.com')
            ->set('answers', [
                (string) $q1->id => (string) $q2Option->id,
                (string) $q2->id => (string) $q2Option->id,
            ])
            ->call('submit')
            ->assertHasErrors(['answers.'.$q1->id]);

        $this->assertSame(0, Submission::query()->count('id'));
    }

    public function test_next_rejects_option_that_belongs_to_another_question(): void
    {
        $quiz = Quiz::create(['title' => 'Test Quiz', 'slug' => 'next-test-quiz', 'description' => 'desc', 'type' => 'quiz', 'is_published' => true]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question' => 'Q1', 'position' => 1, 'points' => 1]);
        $otherQuestion = Question::create(['quiz_id' => $quiz->id, 'question' => 'Q2', 'position' => 2, 'points' => 1]);
        $otherOption = Option::create(['question_id' => $otherQuestion->id, 'label' => 'Other', 'position' => 1]);

        Livewire::test('take-quiz', ['quiz' => $quiz])
            ->set('step', 1)
            ->set('answers.'.$question->id, $otherOption->id)
            ->call('next')
            ->assertHasErrors(['answers.'.$question->id])
            ->assertSet('step', 1);
    }

    public function test_guest_can_submit_without_name_or_email(): void
    {
        $quiz = Quiz::create(['title' => 'Guest Quiz', 'slug' => 'guest-quiz', 'description' => 'desc', 'type' => 'assessment', 'is_published' => true]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question' => 'Q1', 'type' => 'scale', 'position' => 1, 'points' => 1]);
        $option = Option::create(['question_id' => $question->id, 'label' => 'A', 'value' => 1, 'position' => 1]);

        Livewire::test('take-quiz', ['quiz' => $quiz])
            ->set('answers.'.$question->id, $option->id)
            ->call('submit');

        $this->assertDatabaseHas('submissions', ['quiz_id' => $quiz->id, 'participant_name' => null, 'participant_email' => null]);
    }

    public function test_submission_rate_limit_is_scoped_to_the_browser_session(): void
    {
        $quiz = Quiz::create(['title' => 'Limited Quiz', 'slug' => 'limited-quiz', 'description' => 'desc', 'type' => 'assessment', 'is_published' => true]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question' => 'Q1', 'type' => 'scale', 'position' => 1, 'points' => 1]);
        $option = Option::create(['question_id' => $question->id, 'label' => 'A', 'value' => 1, 'position' => 1]);
        $key = 'quiz-submission:'.session()->getId().':'.$quiz->id;
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            Livewire::test('take-quiz', ['quiz' => $quiz])
                ->set('answers.'.$question->id, $option->id)
                ->call('submit');
        }

        Livewire::test('take-quiz', ['quiz' => $quiz])
            ->set('answers.'.$question->id, $option->id)
            ->call('submit')
            ->assertHasErrors('submission');

        $this->assertDatabaseCount('submissions', 5);
    }
}
