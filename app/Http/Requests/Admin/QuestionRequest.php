<?php

namespace App\Http\Requests\Admin;

use App\Enums\QuizDifficulty;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:2000'],
            'difficulty' => ['required', Rule::enum(QuizDifficulty::class)],
            'option_a' => ['required', 'string', 'max:500'],
            'option_b' => ['required', 'string', 'max:500', 'different:option_a'],
            'option_c' => ['required', 'string', 'max:500', 'different:option_a', 'different:option_b'],
            'option_d' => ['required', 'string', 'max:500', 'different:option_a', 'different:option_b', 'different:option_c'],
            'correct_option' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
