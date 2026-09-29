<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnswerQuizQuestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
            'option' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
        ];
    }
}
