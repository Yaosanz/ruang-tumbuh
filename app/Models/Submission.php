<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = ['quiz_id', 'participant_name', 'participant_email', 'score', 'max_score', 'percentage', 'completed_at'];
    protected function casts(): array { return ['completed_at' => 'datetime']; }
    public function quiz(): BelongsTo { return $this->belongsTo(Quiz::class); }
    public function answers(): HasMany { return $this->hasMany(Answer::class); }
}
