<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Rank;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user()->load('team');
        $position = User::query()
            ->where('role', UserRole::Player->value)
            ->where('points', '>', $user->points)
            ->count() + 1;

        $rank = Rank::query()
            ->where('min_points', '<=', $user->points)
            ->orderByDesc('min_points')
            ->value('name') ?? 'Unranked';

        $previousDriverPoints = null;
        $driverPosition = 0;
        $leaderboard = User::query()
            ->with('team:id,name,color')
            ->where('role', UserRole::Player->value)
            ->orderByDesc('points')
            ->orderBy('username')
            ->limit(10)
            ->get(['id', 'username', 'team_id', 'points'])
            ->map(function (User $driver, int $index) use (&$previousDriverPoints, &$driverPosition, $user) {
                if ($previousDriverPoints !== $driver->points) {
                    $driverPosition = $index + 1;
                    $previousDriverPoints = $driver->points;
                }

                return [
                    'position' => $driverPosition,
                    'id' => $driver->id,
                    'username' => $driver->username,
                    'points' => $driver->points,
                    'team' => $driver->team,
                    'isCurrentUser' => $driver->is($user),
                ];
            });

        $previousConstructorPoints = null;
        $constructorPosition = 0;
        $constructors = Team::query()
            ->withSum([
                'users as points_sum' => fn ($query) => $query->where('role', UserRole::Player->value),
            ], 'points')
            ->orderByDesc('points_sum')
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->values()
            ->map(function (Team $team, int $index) use (&$previousConstructorPoints, &$constructorPosition) {
                $points = (int) ($team->points_sum ?? 0);
                if ($previousConstructorPoints !== $points) {
                    $constructorPosition = $index + 1;
                    $previousConstructorPoints = $points;
                }

                return [
                    'position' => $constructorPosition,
                    'name' => $team->name,
                    'color' => $team->color,
                    'points' => $points,
                ];
            });

        return Inertia::render('dashboard', [
            'driver' => [
                'username' => $user->username,
                'points' => $user->points,
                'position' => $position,
                'rank' => $rank,
                'team' => $user->team,
            ],
            'leaderboard' => $leaderboard,
            'constructors' => $constructors,
        ]);
    }
}
