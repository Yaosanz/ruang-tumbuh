<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List options for a question.
     */
    public function index(Quiz $quiz, Question $question): JsonResponse
    {
        $options = $question->options;
        return response()->json(['options' => $options]);
    }

    /**
     * Create an option on a question.
     */
    public function store(Request $request, Quiz $quiz, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|integer',
            'is_correct' => 'boolean',
            'position' => 'nullable|integer|min:0',
            'trait_key' => 'nullable|string|max:10',
        ]);

        $maxPosition = $question->options()->max('position') ?? 0;
        $validated['position'] = $validated['position'] ?? ($maxPosition + 1);
        $validated['is_correct'] = $validated['is_correct'] ?? false;

        $option = $question->options()->create($validated);

        return response()->json([
            'message' => 'Opsi berhasil ditambahkan.',
            'option' => $option,
        ], 201);
    }

    /**
     * Show a specific option.
     */
    public function show(Quiz $quiz, Question $question, Option $option): JsonResponse
    {
        return response()->json(['option' => $option]);
    }

    /**
     * Update an option.
     */
    public function update(Request $request, Quiz $quiz, Question $question, Option $option): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'value' => 'sometimes|integer',
            'is_correct' => 'sometimes|boolean',
            'position' => 'nullable|integer|min:0',
            'trait_key' => 'nullable|string|max:10',
        ]);

        $option->update($validated);

        return response()->json([
            'message' => 'Opsi berhasil diperbarui.',
            'option' => $option->fresh(),
        ]);
    }

    /**
     * Delete an option.
     */
    public function destroy(Quiz $quiz, Question $question, Option $option): JsonResponse
    {
        $option->delete();
        return response()->json(['message' => 'Opsi berhasil dihapus.']);
    }
}
