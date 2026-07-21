<?php

use App\Models\Quiz;
use App\Services\SubmissionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public Quiz $quiz;
    public string $name = '';
    public string $email = '';
    public array $answers = [];
    public int $step = 0;

    public function mount(Quiz $quiz): void
    {
        abort_unless($quiz->is_published && $quiz->questions()->exists(), 404);
        $this->quiz = $quiz->load('questions.options');
    }

    public function start(): void
    {
        $this->validate(['name' => 'nullable|string|max:100', 'email' => 'nullable|email']);
        $this->step = 1;
        $this->resetValidation();
    }

    public function next(): void
    {
        $question = $this->quiz->questions->get($this->step - 1);
        abort_unless($question, 404);

        $selectedOptionRaw = $this->answers[$question->id] ?? '__NOT_SET__';
        $selectedOptionType = gettype($this->answers[$question->id] ?? '__NOT_SET__');
        Log::debug('DEBUG next() called', [
            'step' => $this->step,
            'question_id' => $question->id,
            'question_id_type' => gettype($question->id),
            'answers_array_keys' => array_keys($this->answers),
            'answers_array' => $this->answers,
            'selected_option_raw' => $selectedOptionRaw,
            'selected_option_type' => $selectedOptionType,
            'rules' => $this->answerRules($question->id),
        ]);

        $this->resetValidation();
        try {
            $this->validate($this->answerRules($question->id));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug('DEBUG next() validation FAILED', [
                'errors' => $e->errors(),
                'validator' => $e->validator->failed(),
            ]);
            $this->setErrorBag($e->errors());
            $this->goToFirstInvalidQuestion(array_keys($e->errors()));
            return;
        }

        Log::debug('DEBUG next() validation PASSED');
        $this->step++;
    }

    public function previous(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->resetValidation();
        }
    }

    public function submit(SubmissionService $submissionService): void
    {
        $this->resetValidation();

        Log::debug('DEBUG submit() called', [
            'answers_array' => $this->answers,
            'answers_keys_types' => array_map('gettype', array_keys($this->answers)),
            'answers_values_types' => array_map('gettype', array_values($this->answers)),
            'rules' => $this->submissionRules(),
        ]);

        try {
            $this->validate($this->submissionRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug('DEBUG submit() validation FAILED', [
                'errors' => $e->errors(),
                'validator_failed' => $e->validator->failed(),
            ]);
            $this->setErrorBag($e->errors());
            $this->goToFirstInvalidQuestion(array_keys($e->errors()));
            return;
        }

        Log::debug('DEBUG submit() validation PASSED');

        $rateLimitKey = 'quiz-submission:'.session()->getId().':'.$this->quiz->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $this->addError('submission', 'Terlalu banyak percobaan. Tunggu sebentar sebelum mengirim ulang.');
            return;
        }

        RateLimiter::hit($rateLimitKey, 60);
        $submission = $submissionService->submit($this->quiz, $this->answers, $this->name, $this->email ?: null);

        $this->redirectRoute('results.show', $submission, navigate: true);
    }

    private function submissionRules(): array
    {
        $rules = ['name' => 'nullable|string|max:100', 'email' => 'nullable|email'];

        foreach ($this->quiz->questions as $question) {
            $rules['answers.'.$question->id] = ['required', Rule::exists('options', 'id')->where('question_id', $question->id)];
        }

        return $rules;
    }

    private function answerRules(int $questionId): array
    {
        return ['answers.'.$questionId => ['required', Rule::exists('options', 'id')->where('question_id', $questionId)]];
    }

    private function goToFirstInvalidQuestion(array $errorKeys): void
    {
        if (in_array('name', $errorKeys, true) || in_array('email', $errorKeys, true)) {
            $this->step = 0;
            return;
        }

        foreach ($this->quiz->questions as $index => $question) {
            if (in_array('answers.'.$question->id, $errorKeys, true)) {
                $this->step = $index + 1;
                return;
            }
        }
    }
};
?>

<div class="assessment">
    @if ($step === 0)
        <section class="start-panel">
            <p class="eyebrow">{{ $quiz->type === 'assessment' ? 'SELF ASSESSMENT' : 'QUIZ' }}</p>
            <h1>{{ $quiz->title }}</h1>
            <p>{{ $quiz->description }}</p>
            <div class="form-grid">
                <label>Nama<input wire:model="name" placeholder="Nama panggilan">@error('name')<small class="error">{{ $message }}</small>@enderror</label>
                <label>Email <small>(opsional)</small><input wire:model="email" type="email" placeholder="nama@email.com">@error('email')<small class="error">{{ $message }}</small>@enderror</label>
            </div>
            <button class="button" wire:click="start" wire:loading.attr="disabled">Lanjutkan</button>
        </section>
    @else
        @php($question = $quiz->questions->get($step - 1))
        @php($totalQuestions = $quiz->questions->count())
        <section class="question-panel" wire:key="question-panel-{{ $question->id }}">
            <div class="progress"><span style="width: {{ (($step - 1) / $totalQuestions) * 100 }}%"></span></div>
            <p class="question-count">PERTANYAAN {{ $step }} / {{ $totalQuestions }}</p>
            <h1>{{ $question->question }}</h1>
            <div class="options">
                @foreach ($question->options as $option)
                   <label class="option" wire:key="option-{{ $question->id }}-{{ $option->id }}">
                    <input type="radio" wire:model="answers.{{ $question->id }}" value="{{ $option->id }}" wire:key="radio-{{ $question->id }}-{{ $option->id }}">
                    <span>{{ $option->label }}</span>
                </label>
                @endforeach
            </div>
            @error('answers.'.$question->id)<p class="error">Pilih satu jawaban yang valid untuk melanjutkan.</p>@enderror
            @error('submission')<p class="error">{{ $message }}</p>@enderror
            <div class="actions">
                @if($step > 1)<button class="button secondary" wire:click="previous">Kembali</button>@endif
                @if($step < $totalQuestions)<button class="button" wire:click="next" wire:loading.attr="disabled">Berikutnya</button>@else<button class="button" wire:click="submit" wire:loading.attr="disabled">Lihat hasil</button>@endif
            </div>
        </section>
    @endif
</div>
