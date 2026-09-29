<?php

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use App\Enums\QuizDifficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'difficulty',
        'status',
        'question_count',
        'correct_answers',
        'accuracy',
        'points_awarded',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => QuizDifficulty::class,
            'status' => QuizAttemptStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(QuizAttemptQuestion::class)->orderBy('position');
    }
}
