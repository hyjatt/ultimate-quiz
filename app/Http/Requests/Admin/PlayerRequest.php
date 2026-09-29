<?php

namespace App\Http\Requests\Admin;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayerRequest extends FormRequest
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
            'username' => [
                'required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii',
                Rule::unique(User::class, 'username')->ignore($this->route('player')),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique(User::class, 'email')->ignore($this->route('player')),
            ],
            'team_id' => ['required', 'integer', Rule::exists(Team::class, 'id')->where('is_active', true)],
        ];
    }
}
