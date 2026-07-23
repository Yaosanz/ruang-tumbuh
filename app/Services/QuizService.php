<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\User;
use App\Repositories\QuizRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function __construct(private QuizRepository $quizzes) {}

    public function save(?Quiz $quiz, array $attributes, array $questions, ?User $author): Quiz
    {
        return DB::transaction(function () use ($quiz, $attributes, $questions, $author): Quiz {
            $hasSubmissions = $quiz?->submissions()->exists() ?? false;
            if ($hasSubmissions && ! $this->questionsMatch($quiz, $questions)) {
                throw ValidationException::withMessages([
                    'questions' => ['Pertanyaan tidak dapat diubah setelah assessment memiliki submission. Buat assessment baru untuk revisi struktural.'],
                ]);
            }

            $attributes['slug'] = $quiz?->slug ?? $this->uniqueSlug($attributes['title']);
            $attributes['created_by'] = $quiz?->created_by ?? $author?->id;
            $attributes['passing_score'] = $attributes['type'] === 'quiz' ? $attributes['passing_score'] : null;
            $attributes['interpretation_ranges'] = $attributes['type'] === 'assessment'
                ? $this->interpretationRanges($questions)
                : null;

            $quiz = $quiz
                ? $this->quizzes->update($quiz, $attributes)
                : $this->quizzes->create($attributes);

            if (! $hasSubmissions) {
                $quiz->questions()->delete();
                foreach (array_values($questions) as $position => $question) {
                    $storedQuestion = $quiz->questions()->create([
                        'question' => $question['question'],
                        'type' => $question['type'] ?? 'single_choice',
                        'position' => $position,
                        'points' => $question['points'] ?? 1,
                    ]);

                    foreach (array_values($question['options']) as $optionPosition => $label) {
                        $storedQuestion->options()->create([
                            'label' => $label,
                            'value' => $optionPosition + 1,
                            'is_correct' => $attributes['type'] === 'quiz' && $optionPosition === (int) $question['correct'],
                            'position' => $optionPosition,
                        ]);
                    }
                }
            }

            return $quiz->fresh('questions.options');
        });
    }

    public function togglePublication(Quiz $quiz): Quiz
    {
        return $this->quizzes->update($quiz, ['is_published' => ! $quiz->is_published]);
    }

    public function delete(Quiz $quiz): void
    {
        $this->quizzes->delete($quiz);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'quiz';
        $slug = $base;
        $suffix = 2;

        while (Quiz::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function interpretationRanges(array $questions): array
    {
        $maximum = collect($questions)->sum(fn (array $question) => count($question['options']) * ($question['points'] ?? 1));
        $low = (int) floor($maximum / 3);
        $medium = (int) floor(($maximum * 2) / 3);

        return [
            ['min' => 0, 'max' => $low, 'label' => 'Perlu perhatian lebih'],
            ['min' => $low + 1, 'max' => $medium, 'label' => 'Cukup terkelola'],
            ['min' => $medium + 1, 'max' => $maximum, 'label' => 'Dalam kondisi baik'],
        ];
    }

    private function questionsMatch(Quiz $quiz, array $questions): bool
    {
        $existing = $quiz->loadMissing('questions.options')->questions->map(fn ($question) => [
            'question' => $question->question,
            'type' => $question->type,
            'points' => $question->points,
            'options' => $question->options->pluck('label')->values()->all(),
            'correct' => max(0, $question->options->search(fn ($option) => $option->is_correct)),
        ])->values()->all();

        $incoming = collect($questions)->values()->map(fn (array $question) => [
            'question' => $question['question'],
            'type' => $question['type'] ?? 'single_choice',
            'points' => (int) ($question['points'] ?? 1),
            'options' => array_values($question['options']),
            'correct' => (int) ($question['correct'] ?? 0),
        ])->all();

        return $existing === $incoming;
    }
}
