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
 * @mixin \Eloquent
 */
class Option extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'label', 'value', 'trait_key', 'is_correct', 'position'];
    protected function casts(): array { return ['is_correct' => 'boolean']; }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
}
