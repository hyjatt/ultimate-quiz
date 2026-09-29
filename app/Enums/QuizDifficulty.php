<?php

namespace App\Enums;

enum QuizDifficulty: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function pointsPerAnswer(): int
    {
        return match ($this) {
            self::Easy => 10,
            self::Medium => 20,
            self::Hard => 30,
        };
    }
}
