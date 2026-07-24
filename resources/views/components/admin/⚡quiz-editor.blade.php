<?php

use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public ?Quiz $quiz = null;
    public string $title = '';
    public string $description = '';
    public string $type = 'quiz';
    public ?string $category = 'knowledge';
    public string $assessment_type = '';
    public ?int $duration_minutes = 10;
    public ?int $passing_score = 70;
    public bool $is_published = false;
    public array $questions = [];

    public function mount(?Quiz $quiz = null): void
    {
        if ($quiz?->exists) {
            $this->quiz = $quiz->fresh('questions.options');
            $quiz = $this->quiz;
            $this->title = $quiz->title;
            $this->description = $quiz->description ?? '';
            $this->type = $quiz->type;
            $this->category = $quiz->category;
            $this->assessment_type = $quiz->assessment_type ?? '';
            $this->duration_minutes = $quiz->duration_minutes;
            $this->passing_score = $quiz->passing_score;
            $this->is_published = $quiz->is_published;
            $this->questions = $quiz->questions->map(fn ($question) => [
                'question' => $question->question,
                'type' => $question->type,
                'points' => $question->points,
                'options' => $question->options->pluck('label')->all(),
                'trait_keys' => $question->options->pluck('trait_key')->all(),
                'correct' => max(0, $question->options->search(fn ($option) => $option->is_correct)),
            ])->all();
        }

        if (! $this->questions) $this->addQuestion();
    }

    public function isTraitAssessment(): bool
    {
        return in_array($this->category, ['personality', 'psychological']);
    }

    public function addQuestion(): void
    {
        $this->questions[] = ['question' => '', 'type' => 'single_choice', 'points' => 1, 'options' => ['', '', '', ''], 'trait_keys' => ['', '', '', ''], 'correct' => 0];
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function moveQuestion(int $index, int $direction): void
    {
        $target = $index + $direction;
        if (! isset($this->questions[$target])) return;
        [$this->questions[$index], $this->questions[$target]] = [$this->questions[$target], $this->questions[$index]];
    }

    public function addOption(int $questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = '';
        $this->questions[$questionIndex]['trait_keys'][] = '';
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (count($this->questions[$questionIndex]['options']) <= 2) return;
        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
        unset($this->questions[$questionIndex]['trait_keys'][$optionIndex]);
        $this->questions[$questionIndex]['trait_keys'] = array_values($this->questions[$questionIndex]['trait_keys']);
        $this->questions[$questionIndex]['correct'] = min($this->questions[$questionIndex]['correct'], count($this->questions[$questionIndex]['options']) - 1);
    }

    public function moveOption(int $questionIndex, int $optionIndex, int $direction): void
    {
        $target = $optionIndex + $direction;
        if (! isset($this->questions[$questionIndex]['options'][$target])) return;
        [$this->questions[$questionIndex]['options'][$optionIndex], $this->questions[$questionIndex]['options'][$target]] = [$this->questions[$questionIndex]['options'][$target], $this->questions[$questionIndex]['options'][$optionIndex]];
        [$this->questions[$questionIndex]['trait_keys'][$optionIndex], $this->questions[$questionIndex]['trait_keys'][$target]] = [$this->questions[$questionIndex]['trait_keys'][$target], $this->questions[$questionIndex]['trait_keys'][$optionIndex]];
        $correct = $this->questions[$questionIndex]['correct'];
        if ($correct === $optionIndex) $this->questions[$questionIndex]['correct'] = $target;
        elseif ($correct === $target) $this->questions[$questionIndex]['correct'] = $optionIndex;
    }

    public function save(QuizService $quizService): void
    {
        $this->questions = array_map(fn (array $question) => array_replace([
            'type' => 'single_choice', 'points' => 1, 'options' => [], 'trait_keys' => [], 'correct' => 0
        ], $question), $this->questions);
        $this->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string',
            'type' => 'required|in:quiz,assessment',
            'category' => 'required|in:knowledge,skill,psychological,personality',
            'assessment_type' => 'nullable|string|max:80',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'duration_minutes' => 'nullable|integer|min:1|max:240',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:single_choice,scale',
            'questions.*.points' => 'required|integer|min:1|max:100',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*' => 'required|string|max:255',
            'questions.*.trait_keys.*' => 'nullable|string|max:20',
            'questions.*.correct' => 'required_if:type,quiz|integer|min:0',
        ]);

        foreach ($this->questions as $index => $question) {
            if ($this->type === 'quiz' && $question['correct'] >= count($question['options'])) {
                $this->addError('questions.'.$index.'.correct', 'Jawaban benar harus menunjuk ke salah satu opsi.');
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) return;

        $quizService->save($this->quiz, [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'category' => $this->category,
            'assessment_type' => $this->assessment_type ?: null,
            'duration_minutes' => $this->duration_minutes,
            'passing_score' => $this->passing_score,
            'is_published' => $this->is_published,
        ], $this->questions, Auth::user());

        $this->redirectRoute('admin.dashboard');
    }
};
?>

<div>
    <header class="admin-header"><div><a class="text-link" href="{{ route('admin.dashboard') }}">Kembali</a><h1>{{ $quiz ? 'Edit assessment' : 'Assessment baru' }}</h1></div><button class="button" wire:click="save">Simpan</button></header>
    <section class="editor">
        <label>Judul<input wire:model="title"></label>
        <label>Deskripsi<textarea wire:model="description" rows="3"></textarea></label>
        <div class="form-grid">
            <label>Tipe<select wire:model.live="type"><option value="quiz">Quiz</option><option value="assessment">Assessment</option></select></label>
            <label>Kategori<select wire:model="category"><option value="knowledge">Knowledge</option><option value="skill">Skill</option><option value="psychological">Psychological</option><option value="personality">Personality</option></select></label>
            <label>Durasi (menit)<input wire:model="duration_minutes" type="number" min="1"></label>
            @if($type === 'quiz')<label>Skor minimal lulus (%)<input wire:model="passing_score" type="number" min="0" max="100"></label>@endif
        </div>
        @if(in_array($category, ['psychological', 'personality']))<label>Jenis assessment<input wire:model="assessment_type" placeholder="Mis. Stress atau DISC"></label>@endif
        <label class="check"><input wire:model="is_published" type="checkbox"> Publikasikan sekarang</label>
        <h2>Pertanyaan</h2>
        @error('questions')<p class="error">{{ $message }}</p>@enderror
        @foreach($questions as $i => $question)
            <article class="editor-question" wire:key="question-{{ $i }}">
                <div class="question-heading"><strong>{{ $i + 1 }}</strong><span><button class="text-link" wire:click="moveQuestion({{ $i }}, -1)">Naik</button><button class="text-link" wire:click="moveQuestion({{ $i }}, 1)">Turun</button><button class="text-link danger" wire:click="removeQuestion({{ $i }})">Hapus</button></span></div>
                <label>Pertanyaan<input wire:model="questions.{{ $i }}.question"></label>
                <div class="form-grid"><label>Tipe soal<select wire:model="questions.{{ $i }}.type"><option value="single_choice">Pilihan tunggal</option><option value="scale">Skala</option></select></label><label>Bobot skor<input wire:model="questions.{{ $i }}.points" type="number" min="1"></label></div>
                @foreach($question['options'] as $j => $option)
                    <label class="option-edit" wire:key="option-{{ $i }}-{{ $j }}">
                        <input wire:model="questions.{{ $i }}.options.{{ $j }}" placeholder="Pilihan {{ $j + 1 }}">
                        @if(in_array($category, ['personality', 'psychological']))
                            <input wire:model="questions.{{ $i }}.trait_keys.{{ $j }}" placeholder="Trait key (mis. E, I, D, O)" class="trait-input">
                        @endif
                        @if($type === 'quiz')<input type="radio" wire:model="questions.{{ $i }}.correct" value="{{ $j }}" title="Jawaban benar">@endif
                        <button class="text-link" wire:click="moveOption({{ $i }}, {{ $j }}, -1)">Naik</button>
                        <button class="text-link" wire:click="moveOption({{ $i }}, {{ $j }}, 1)">Turun</button>
                        <button class="text-link danger" wire:click="removeOption({{ $i }}, {{ $j }})">Hapus</button>
                    </label>
                @endforeach
                <button class="text-link" wire:click="addOption({{ $i }})">+ Tambah opsi</button>
            </article>
        @endforeach
        <button class="button secondary" wire:click="addQuestion">+ Tambah pertanyaan</button>
    </section>
</div>
