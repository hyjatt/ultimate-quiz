<?php

namespace App\Http\Controllers\Settings;

use App\Enums\QuizAttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\QuizAttempt;
use App\Models\Team;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'teams' => Team::query()
                ->where('is_active', true)
                ->orWhereKey($request->user()->team_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'stats' => [
                'totalRaces' => QuizAttempt::query()
                    ->whereBelongsTo($request->user())
                    ->where('status', QuizAttemptStatus::Completed->value)
                    ->count(),
                'averageAccuracy' => round((float) QuizAttempt::query()
                    ->whereBelongsTo($request->user())
                    ->where('status', QuizAttemptStatus::Completed->value)
                    ->avg('accuracy'), 1),
            ],
            'history' => QuizAttempt::query()
                ->whereBelongsTo($request->user())
                ->where('status', QuizAttemptStatus::Completed->value)
                ->latest('completed_at')
                ->limit(5)
                ->get(['id', 'difficulty', 'accuracy', 'points_awarded', 'completed_at']),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('status', 'Driver profile updated.');
    }
}
