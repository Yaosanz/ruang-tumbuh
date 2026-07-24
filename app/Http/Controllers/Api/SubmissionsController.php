<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;

class SubmissionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List all submissions.
     */
    public function index(): JsonResponse
    {
        $submissions = Submission::with('quiz')->latest()->get();
        return SubmissionResource::collection($submissions)->response(request());
    }

    /**
     * Show a specific submission.
     */
    public function show(Submission $submission): JsonResponse
    {
        $submission->load(['quiz', 'answers.question', 'answers.option']);
        return (new SubmissionResource($submission))->response(request());
    }

    /**
     * Delete a submission.
     */
    public function destroy(Submission $submission): JsonResponse
    {
        $submission->answers()->delete();
        $submission->delete( request()->user());
        return response()->json(['message' => 'Submission berhasil dihapus.']);
    }
}
