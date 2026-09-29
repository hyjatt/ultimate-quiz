<?php

namespace App\Http\Requests;

use App\Enums\QuizDifficulty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartQuizAttemptRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'difficulty' => ['required', Rule::enum(QuizDifficulty::class)],
        ];
    }
}
