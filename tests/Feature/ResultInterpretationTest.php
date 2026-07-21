<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResultInterpretationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_interpretation_uses_ranges(): void
    {
        $quiz = Quiz::create([
            'title' => 'Test',
            'slug' => 'test-interp',
            'description' => 'Test',
            'type' => 'assessment',
            'is_published' => true,
            'interpretation_ranges' => [
                ['min' => 0, 'max' => 5, 'label' => 'Rendah'],
                ['min' => 6, 'max' => 10, 'label' => 'Sedang'],
                ['min' => 11, 'max' => 20, 'label' => 'Tinggi'],
            ],
        ]);

        $submission = Submission::create([
            'quiz_id' => $quiz->id,
            'participant_name' => 'Test',
            'score' => 3,
            'max_score' => 20,
            'percentage' => 15,
            'completed_at' => now(),
        ]);

        $component = Livewire::test('result-page', ['submission' => $submission]);
        $this->assertEquals('Rendah', $component->instance()->interpretation());
    }

    public function test_mid_range_interpretation(): void
    {
        $quiz = Quiz::create([
            'title' => 'Test',
            'slug' => 'test-interp-mid',
            'description' => 'Test',
            'type' => 'assessment',
            'is_published' => true,
            'interpretation_ranges' => [
                ['min' => 0, 'max' => 5, 'label' => 'Rendah'],
                ['min' => 6, 'max' => 10, 'label' => 'Sedang'],
                ['min' => 11, 'max' => 20, 'label' => 'Tinggi'],
            ],
        ]);

        $submission = Submission::create([
            'quiz_id' => $quiz->id,
            'participant_name' => 'Test',
            'score' => 8,
            'max_score' => 20,
            'percentage' => 40,
            'completed_at' => now(),
        ]);

        $component = Livewire::test('result-page', ['submission' => $submission]);
        $this->assertEquals('Sedang', $component->instance()->interpretation());
    }

    public function test_quiz_interpretation_uses_passing_score(): void
    {
        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'slug' => 'test-quiz-interp',
            'description' => 'Test',
            'type' => 'quiz',
            'is_published' => true,
            'passing_score' => 70,
        ]);

        $passed = Submission::create([
            'quiz_id' => $quiz->id,
            'participant_name' => 'Passed',
            'score' => 7,
            'max_score' => 10,
            'percentage' => 70,
            'completed_at' => now(),
        ]);

        $component = Livewire::test('result-page', ['submission' => $passed]);
        $this->assertEquals('Lulus', $component->instance()->interpretation());

        $failed = Submission::create([
            'quiz_id' => $quiz->id,
            'participant_name' => 'Failed',
            'score' => 5,
            'max_score' => 10,
            'percentage' => 50,
            'completed_at' => now(),
        ]);

        $component = Livewire::test('result-page', ['submission' => $failed]);
        $this->assertEquals('Belum lulus', $component->instance()->interpretation());
    }

    public function test_fallback_interpretation(): void
    {
        $quiz = Quiz::create([
            'title' => 'Test',
            'slug' => 'test-fallback',
            'description' => 'Test',
            'type' => 'assessment',
            'is_published' => true,
        ]);

        $submission = Submission::create([
            'quiz_id' => $quiz->id,
            'participant_name' => 'Test',
            'score' => 0,
            'max_score' => 10,
            'percentage' => 0,
            'completed_at' => now(),
        ]);

        $component = Livewire::test('result-page', ['submission' => $submission]);
        $this->assertEquals('Hasil refleksi Anda', $component->instance()->interpretation());
    }
}
