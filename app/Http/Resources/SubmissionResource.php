<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'quiz_id' => $this->quiz_id,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'percentage' => $this->percentage,
            'result_summary' => $this->result_summary,
            'completed_at' => $this->completed_at,
        ];
    }
}
