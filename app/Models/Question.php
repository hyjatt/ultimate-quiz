<?php

namespace App\Models;

use App\Enums\QuizDifficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt',
        'options',
        'correct_option',
        'difficulty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'difficulty' => QuizDifficulty::class,
            'is_active' => 'boolean',
        ];
    }
}
