<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $quiz_id
 * @property string $question
 * @property string $type
 * @property int $position
 * @property int $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Option> $options
 * @property-read int|null $options_count
 * @property-read \App\Models\Quiz $quiz
 * @mixin \Eloquent
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_id', 'question', 'type', 'position', 'points'];
    public function quiz(): BelongsTo { return $this->belongsTo(Quiz::class); }
    public function options(): HasMany { return $this->hasMany(Option::class)->orderBy('position'); }
}
