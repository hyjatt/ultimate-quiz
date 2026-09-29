<?php

namespace App\Concerns;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null, ?int $currentTeamId = null): array
    {
        return [
            'username' => $this->usernameRules($userId),
            'email' => $this->emailRules($userId),
            'team_id' => [
                'required',
                'integer',
                Rule::exists(Team::class, 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->when($currentTeamId !== null, fn ($teamQuery) => $teamQuery->orWhere('id', $currentTeamId))),
            ],
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function usernameRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'min:3',
            'max:50',
            'alpha_dash:ascii',
            $userId === null
                ? Rule::unique(User::class, 'username')
                : Rule::unique(User::class, 'username')->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
