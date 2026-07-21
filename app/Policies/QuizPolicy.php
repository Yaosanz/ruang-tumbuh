<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Quiz $quiz): bool { return true; }
    public function create(User $user): bool { return $user->role === 'admin'; }
    public function update(User $user, Quiz $quiz): bool { return $user->role === 'admin'; }
    public function delete(User $user, Quiz $quiz): bool { return $user->role === 'admin'; }
}

