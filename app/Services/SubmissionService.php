<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\Submission;
use App\Repositories\SubmissionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionService
{
    public function __construct(private SubmissionRepository $submissions, private ScoringService $scoring) {}

    public function submit(Quiz $quiz, array $answers, ?string $name, ?string $email): Submission
    {
        $selectedOptions = $this->resolveOptions($quiz, $answers);
        $result = $this->scoring->calculate($quiz, $selectedOptions);

        return DB::transaction(function () use ($quiz, $selectedOptions, $result, $name, $email): Submission {
            $submission = $this->submissions->create([
                'quiz_id' => $quiz->id,
                'user_id' => Auth::id(),
                'guest_identifier' => Auth::check() ? null : $this->guestIdentifier(),
                'participant_name' => $name ?: null,
                'participant_email' => $email,
                'score' => $result['score'],
                'max_score' => $result['max_score'],
                'percentage' => $result['percentage'],
                'result_summary' => $result['summary'],
                'started_at' => now(),
                'completed_at' => now(),
                'expires_at' => now()->addHours(24),
            ]);

            foreach ($quiz->questions as $question) {
                $option = $selectedOptions->get($question->id);
                $this->submissions->saveAnswer($submission, $question->id, $option->id, $option->value);
            }

            return $submission;
        });
    }

    private function resolveOptions(Quiz $quiz, array $answers): Collection
    {
        $selected = collect();

        foreach ($quiz->questions as $question) {
            $option = $question->options->firstWhere('id', (int) ($answers[$question->id] ?? 0));
            if (! $option) {
                throw ValidationException::withMessages([
                    'answers.'.$question->id => ['Pilihan jawaban tidak valid untuk pertanyaan ini.'],
                ]);
            }
            $selected->put($question->id, $option);
        }

        return $selected;
    }

    private function guestIdentifier(): string
    {
        if (! request()->hasSession()) return (string) Str::uuid();
        if (session()->has('guest_identifier')) return session('guest_identifier');
        $identifier = (string) Str::uuid();
        session(['guest_identifier' => $identifier]);
        return $identifier;
    }
}
