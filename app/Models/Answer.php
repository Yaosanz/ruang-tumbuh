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
 * @method static \Database\Factories\AnswerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereSubmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Answer whereValue($value)
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
