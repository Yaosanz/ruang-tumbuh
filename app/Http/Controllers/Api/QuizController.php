<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Quiz;
use App\Services\SubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    public function __construct(
        private readonly SubmissionService $submissionService,
    ) {}

    /**
     * List all published quizzes.
     */
    public function index(): JsonResponse
    {
        $quizzes = Quiz::where('is_published', true)
            ->withCount('questions')
            ->latest()
            ->get();

        return QuizResource::collection($quizzes)->response();
    }

    /**
     * Show a specific quiz with its questions and options.
     */
    public function show(Quiz $quiz): JsonResponse
    {
        abort_unless($quiz->is_published && $quiz->questions()->exists(), 404);

        $quiz->load('questions.options');

        return (new QuizResource($quiz))->response();
    }

    /**
     * Submit answers for a quiz.
     */
    public function submit(Request $request, Quiz $quiz): JsonResponse
    {
        abort_unless($quiz->is_published && $quiz->questions()->exists(), 404);

        $quiz->load('questions.options');

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'answers' => 'required|array',
            'answers.*' => ['required', 'integer', Rule::exists('options', 'id')],
        ]);

        $submission = $this->submissionService->submit(
            $quiz,
            $validated['answers'],
            $validated['name'] ?? null,
            $validated['email'] ?? null,
        );

        return (new SubmissionResource($submission))
            ->response()
            ->setStatusCode(201);
    }
}
