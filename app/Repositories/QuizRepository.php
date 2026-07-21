<?php

namespace App\Repositories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;

class QuizRepository
{
    public function published(): Collection
    {
        return Quiz::query()->where('is_published', true)->withCount('questions')->latest()->get();
    }

    public function findPublishedBySlug(string $slug): ?Quiz
    {
        return Quiz::query()->where('slug', $slug)->where('is_published', true)->with('questions.options')->first();
    }

    public function create(array $attributes): Quiz
    {
        return Quiz::create($attributes);
    }

    public function update(Quiz $quiz, array $attributes): Quiz
    {
        $quiz->update($attributes);
        return $quiz->refresh();
    }

    public function delete(Quiz $quiz): void
    {
        $quiz->delete();
    }
}
