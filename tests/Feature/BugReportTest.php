<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BugReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_with_string_option_id_from_browser(): void
    {
        // Create an assessment quiz (like "cek-kondisi-stres")
        $quiz = Quiz::create([
            'title' => 'Stress Check',
            'slug' => 'cek-kondisi-stres',
            'description' => 'desc',
            'type' => 'assessment',
            'is_published' => true,
        ]);

        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Seberapa sering?',
            'type' => 'single_choice',
            'position' => 1,
            'points' => 1,
        ]);

        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Seberapa berat?',
            'type' => 'single_choice',
            'position' => 2,
            'points' => 1,
        ]);

        $optA = Option::create([
            'question_id' => $q1->id,
            'label' => 'Sering',
            'value' => 3,
            'is_correct' => false,  // Assessment: ALL options have is_correct=false
            'position' => 1,
        ]);

        $optB = Option::create([
            'question_id' => $q1->id,
            'label' => 'Jarang',
            'value' => 1,
            'is_correct' => false,
            'position' => 2,
        ]);

        Option::create([
            'question_id' => $q2->id,
            'label' => 'Ringan',
            'value' => 1,
            'is_correct' => false,
            'position' => 1,
        ]);

        // Simulate EXACTLY what browser sends: string IDs via wire:model
        // Radio button HTML: value="{{ $option->id }}" → value="1" (string)
        // wire:model sends: answers.{question_id} = "option_id_string"

        $component = Livewire::test('take-quiz', ['quiz' => $quiz]);

        // Step 1: Start the quiz (fill name, go to step 1)
        $component->set('name', 'Test User')
            ->call('start')
            ->assertSet('step', 1);

        // Simulate clicking radio button "Sering" (option ID = 1)
        // Browser sends string "1" via wire:model
        $component->set('answers.'.$q1->id, (string) $optA->id);

        // Verify the answer was stored
        $component->assertSet('answers.'.$q1->id, (string) $optA->id);

        // Now click "Berikutnya"
        $component->call('next');

        // This should pass validation and move to step 2
        $component->assertSet('step', 2);
        $component->assertHasNoErrors('answers.'.$q1->id);
    }

    public function test_full_assessment_submission_with_string_ids(): void
    {
        $quiz = Quiz::create([
            'title' => 'Stress Check',
            'slug' => 'cek-kondisi-stres',
            'description' => 'desc',
            'type' => 'assessment',
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

        $q1Opt = Option::create([
            'question_id' => $q1->id,
            'label' => 'A',
            'value' => 2,
            'is_correct' => false,
            'position' => 1,
        ]);

        $q2Opt = Option::create([
            'question_id' => $q2->id,
            'label' => 'B',
            'value' => 3,
            'is_correct' => false,
            'position' => 1,
        ]);

        // Simulate browser: all values arrive as strings
        $answers = [
            (string) $q1->id => (string) $q1Opt->id,
            (string) $q2->id => (string) $q2Opt->id,
        ];

        $component = Livewire::test('take-quiz', ['quiz' => $quiz])
            ->set('name', 'Test')
            ->call('start')
            ->assertSet('step', 1);

        // Step through question 1
        $component->set('answers.'.$q1->id, (string) $q1Opt->id);
        $component->call('next');
        $component->assertSet('step', 2);

        // Set answer for question 2
        $component->set('answers.'.$q2->id, (string) $q2Opt->id);

        // Now submit
        $component->call('submit');

        // Should have redirected to results
        $component->assertRedirect();
    }
}
