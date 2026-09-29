<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuizAttemptStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $completedAttempts = QuizAttempt::query()->where('status', QuizAttemptStatus::Completed->value);

        return Inertia::render('admin/dashboard', [
            'metrics' => [
                'players' => User::query()->where('role', UserRole::Player->value)->count(),
                'suspendedPlayers' => User::query()->where('role', UserRole::Player->value)->whereNotNull('suspended_at')->count(),
                'activeQuestions' => Question::query()->where('is_active', true)->count(),
                'attemptsToday' => QuizAttempt::query()->whereDate('created_at', today())->count(),
                'completedAttempts' => (clone $completedAttempts)->count(),
                'averageAccuracy' => (int) round((float) ((clone $completedAttempts)->avg('accuracy') ?? 0)),
            ],
            'questionsByDifficulty' => Question::query()
                ->selectRaw('difficulty, count(*) as total')
                ->groupBy('difficulty')
                ->pluck('total', 'difficulty'),
            'recentActivity' => AuditLog::query()
                ->with('actor:id,username')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (AuditLog $log): array => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor' => $log->actor?->username ?? 'System',
                    'created_at' => $log->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
