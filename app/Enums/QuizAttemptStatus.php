<?php

namespace App\Enums;

enum QuizAttemptStatus: string
{
    case Started = 'started';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
}
