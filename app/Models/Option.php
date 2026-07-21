<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $question_id
 * @property string $label
 * @property int $value
 * @property bool $is_correct
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @method static \Database\Factories\OptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereValue($value)
 * @mixin \Eloquent
 */
class Option extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'label', 'value', 'trait_key', 'is_correct', 'position'];
    protected function casts(): array { return ['is_correct' => 'boolean']; }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
}
