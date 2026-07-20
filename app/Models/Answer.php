<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['submission_id', 'question_id', 'option_id', 'value'];
    public function submission(): BelongsTo { return $this->belongsTo(Submission::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function option(): BelongsTo { return $this->belongsTo(Option::class); }
}
