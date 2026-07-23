<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property int|null $duration_minutes
 * @property int|null $passing_score
 * @property bool $is_published
 * @property array<array-key, mixed>|null $interpretation_ranges
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 * @property-read int|null $questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submission> $submissions
 * @property-read int|null $submissions_count
 * @mixin \Eloquent
 */
class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'type', 'category', 'assessment_type', 'duration_minutes', 'passing_score', 'is_published', 'interpretation_ranges', 'created_by'];
    protected function casts(): array { return ['is_published' => 'boolean', 'interpretation_ranges' => 'array']; }
    public function getRouteKeyName(): string { return 'slug'; }
    public function questions(): HasMany { return $this->hasMany(Question::class)->orderBy('position'); }
    public function submissions(): HasMany { return $this->hasMany(Submission::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
