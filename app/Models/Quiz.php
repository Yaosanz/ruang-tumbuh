<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'type', 'duration_minutes', 'passing_score', 'is_published', 'interpretation_ranges'];
    protected function casts(): array { return ['is_published' => 'boolean', 'interpretation_ranges' => 'array']; }
    public function getRouteKeyName(): string { return 'slug'; }
    public function questions(): HasMany { return $this->hasMany(Question::class)->orderBy('position'); }
    public function submissions(): HasMany { return $this->hasMany(Submission::class); }
}
