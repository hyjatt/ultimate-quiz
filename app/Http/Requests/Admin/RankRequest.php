<?php

namespace App\Http\Requests\Admin;

use App\Models\Rank;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RankRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:50', Rule::unique(Rank::class)->ignore($this->route('rank'))],
            'min_points' => [
                'required', 'integer', 'min:0',
                Rule::unique(Rank::class, 'min_points')->ignore($this->route('rank')),
            ],
        ];
    }
}
