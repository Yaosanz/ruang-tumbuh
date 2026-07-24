<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List questions for a quiz.
     */
    public function index(Quiz $quiz): JsonResponse
    {
        $questions = $quiz->questions()->with('options')->get();
        return response()->json(['questions' => $questions]);
    }

    /**
     * Create a question on a quiz.
     */
    public function store(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(['single_choice', 'multiple_choice', 'likert'])],
            'points' => 'integer|min:0',
            'position' => 'nullable|integer|min:0',
        ]);

        $maxPosition = $quiz->questions()->max('position') ?? 0;
        $validated['position'] = $validated['position'] ?? ($maxPosition + 1);
        $validated['quiz_id'] = $quiz->id;

        $question = Question::create($validated);
        $question->load('options');

        return response()->json([
            'message' => 'Pertanyaan berhasil ditambahkan.',
            'question' => $question,
        ], 201);
    }

    /**
     * Show a specific question.
     */
    public function show(Quiz $quiz, Question $question): JsonResponse
    {
        $question->load('options');
        return response()->json(['question' => $question]);
    }

    /**
     * Update a question.
     */
    public function update(Request $request, Quiz $quiz, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'sometimes|string',
            'type' => ['sometimes', 'string', \Illuminate\Validation\Rule::in(['single_choice', 'multiple_choice', 'likert'])],
            'points' => 'sometimes|integer|min:0',
            'position' => 'nullable|integer|min:0',
        ]);

        $question->update($validated);
        $question->load('options');

        return response()->json([
            'message' => 'Pertanyaan berhasil diperbarui.',
            'question' => $question,
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroy(Quiz $quiz, Question $question): JsonResponse
    {
        $question->options()->delete();
        $question->delete();

        return response()->json(['message' => 'Pertanyaan berhasil dihapus.']);
    }
}
