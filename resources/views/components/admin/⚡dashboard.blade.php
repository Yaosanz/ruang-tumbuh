<?php

use App\Models\Quiz;
use App\Models\Submission;
use App\Services\QuizService;
use Livewire\Component;

new class extends Component {
    public function with(): array
    {
        return ['quizzes' => Quiz::withCount(['questions', 'submissions'])->latest()->get(), 'submissionCount' => Submission::count()];
    }

    public function toggle(int $id, QuizService $quizService): void
    {
        $quizService->togglePublication(Quiz::findOrFail($id));
    }

    public function delete(int $id, QuizService $quizService): void
    {
        $quizService->delete(Quiz::findOrFail($id));
    }

    public function logout(): void
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirectRoute('admin.login');
    }
};
?>

<div>
    <header class="admin-header"><div><p class="eyebrow">ADMIN CMS</p><h1>Assessment</h1></div><div class="header-actions"><a class="button" href="{{ route('admin.quizzes.editor') }}">+ Buat assessment</a><button class="icon-button" title="Keluar" wire:click="logout">Keluar</button></div></header>
    <section class="stats"><div><span>Total assessment</span><strong>{{ $quizzes->count() }}</strong></div><div><span>Total submission</span><strong>{{ $submissionCount }}</strong></div><div><span>Dipublikasikan</span><strong>{{ $quizzes->where('is_published', true)->count() }}</strong></div></section>
    <section class="table-wrap"><table><thead><tr><th>Assessment</th><th>Tipe</th><th>Pertanyaan</th><th>Submission</th><th>Status</th><th></th></tr></thead><tbody>@forelse($quizzes as $quiz)<tr><td><strong>{{ $quiz->title }}</strong><small>{{ $quiz->description }}</small></td><td>{{ ucfirst($quiz->type) }}</td><td>{{ $quiz->questions_count }}</td><td>{{ $quiz->submissions_count }}</td><td><button class="status {{ $quiz->is_published ? 'live' : '' }}" wire:click="toggle({{ $quiz->id }})">{{ $quiz->is_published ? 'Live' : 'Draft' }}</button></td><td><a class="text-link" href="{{ route('admin.quizzes.editor', $quiz) }}">Edit</a> <button class="text-link danger" wire:click="delete({{ $quiz->id }})" wire:confirm="Hapus assessment ini beserta semua pertanyaan dan submission?">Hapus</button></td></tr>@empty<tr><td colspan="6">Belum ada assessment.</td></tr>@endforelse</tbody></table></section>
</div>
