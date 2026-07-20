<?php
use Livewire\Component;
new class extends Component { public function with(): array { return ['quizzes' => \App\Models\Quiz::where('is_published', true)->withCount('questions')->latest()->get()]; } };
?>
<div class="quiz-grid">@forelse ($quizzes as $quiz)<article class="quiz-card"><p class="card-kicker">{{ $quiz->type === 'assessment' ? 'SELF ASSESSMENT' : 'QUIZ' }}</p><h2>{{ $quiz->title }}</h2><p>{{ $quiz->description }}</p><div class="card-meta"><span>{{ $quiz->questions_count }} pertanyaan</span><span>{{ $quiz->duration_minutes ? $quiz->duration_minutes.' menit' : 'Tanpa batas waktu' }}</span></div><a class="button" href="{{ route('quizzes.take', $quiz) }}">Mulai</a></article>@empty <div class="empty-state"><h2>Belum ada assessment tersedia</h2><p>Silakan kembali lagi saat assessment telah dipublikasikan.</p></div>@endforelse</div>
