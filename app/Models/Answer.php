<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $submission_id
 * @property int $question_id
 * @property int|null $option_id
 * @property int|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Option|null $option
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\Submission $submission
 * @mixin \Eloquent
 */
class Answer extends Model
{
    use HasFactory;

    protected $fillable = ['submission_id', 'question_id', 'option_id', 'value'];
    public function submission(): BelongsTo { return $this->belongsTo(Submission::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function option(): BelongsTo { return $this->belongsTo(Option::class); }
}
