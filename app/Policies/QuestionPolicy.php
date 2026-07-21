<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Question $question): bool { return true; }
    public function create(User $user): bool { return $user->role === 'admin'; }
    public function update(User $user, Question $question): bool { return $user->role === 'admin'; }
    public function delete(User $user, Question $question): bool { return $user->role === 'admin'; }
}

