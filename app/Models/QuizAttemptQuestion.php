<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'position',
        'selected_option',
        'is_correct',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
