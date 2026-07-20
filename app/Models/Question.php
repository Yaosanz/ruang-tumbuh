<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['quiz_id', 'question', 'type', 'position', 'points'];
    public function quiz(): BelongsTo { return $this->belongsTo(Quiz::class); }
    public function options(): HasMany { return $this->hasMany(Option::class)->orderBy('position'); }
}
