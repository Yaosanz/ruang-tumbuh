<?php

namespace App\Livewire;

use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $submissions = Submission::query()
            ->where('user_id', Auth::id())
            ->with('quiz')
            ->latest()
            ->get();

        $stats = [
            'total' => $submissions->count(),
            'avg_score' => $submissions->avg('percentage'),
            'quizzes' => $submissions->pluck('quiz.title')->unique()->values(),
        ];

        return view('livewire.dashboard', compact('submissions', 'stats'));
    }
}

