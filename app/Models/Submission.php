<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $quiz_id
 * @property string $participant_name
 * @property string|null $participant_email
 * @property int $score
 * @property int $max_score
 * @property int $percentage
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Answer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Quiz $quiz
 * @mixin \Eloquent
 */
class Submission extends Model
{
    use HasFactory;

    protected $fillable = ['public_id', 'quiz_id', 'user_id', 'guest_identifier', 'participant_name', 'participant_email', 'score', 'max_score', 'percentage', 'result_summary', 'started_at', 'completed_at'];
    protected function casts(): array { return ['result_summary' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime']; }
    protected static function booted(): void { static::creating(fn (Submission $submission) => $submission->public_id ??= (string) Str::uuid()); }
    public function getRouteKeyName(): string { return 'public_id'; }
    public function quiz(): BelongsTo { return $this->belongsTo(Quiz::class); }
    public function answers(): HasMany { return $this->hasMany(Answer::class); }
}
