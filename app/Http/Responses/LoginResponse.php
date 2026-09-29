<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        return redirect()->intended($user->isAdministrator() ? route('admin.dashboard') : route('dashboard'));
    }
}
