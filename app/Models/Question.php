<?php

namespace App\Models;

use App\Enums\QuizDifficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt',
        'options',
        'correct_option',
        'difficulty',
        'is_active',
        'revises_question_id',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'difficulty' => QuizDifficulty::class,
            'is_active' => 'boolean',
        ];
    }

    public function revisedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'revises_question_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'revises_question_id');
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(QuizAttemptQuestion::class);
    }
}
