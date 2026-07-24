<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizzesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show']);
    }

    /**
     * List all quizzes (public).
     */
    public function index(): JsonResponse
    {
        $quizzes = Quiz::withCount('questions')->latest()->get();
        return QuizResource::collection($quizzes)->response();
    }

    /**
     * Create a new quiz.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(['assessment', 'quiz'])],
            'duration_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'is_published' => 'boolean',
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(5),
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'passing_score' => $validated['passing_score'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return (new QuizResource($quiz))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a specific quiz.
     */
    public function show(Quiz $quiz): JsonResponse
    {
        $quiz->load('questions.options');
        return (new QuizResource($quiz))->response();
    }

    /**
     * Update a quiz.
     */
    public function update(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => ['sometimes', 'string', \Illuminate\Validation\Rule::in(['assessment', 'quiz'])],
            'duration_minutes' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'is_published' => 'boolean',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        $quiz->update($validated);
        $quiz->load('questions.options');

        return (new QuizResource($quiz))->response();
    }

    /**
     * Delete a quiz.
     */
    public function destroy(Quiz $quiz): JsonResponse
    {
        $quiz->questions()->delete();
        $quiz->submissions()->delete();
        $quiz->delete();

        return response()->json(['message' => 'Quiz berhasil dihapus.']);
    }
}
