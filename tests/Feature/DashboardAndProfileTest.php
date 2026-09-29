<?php

namespace Tests\Feature;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;
use App\Models\Rank;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_reports_rank_ties_and_constructor_totals(): void
    {
        $team = Team::create(['name' => 'Ferrari', 'color' => '#e80020']);
        Rank::create(['name' => 'Rookie', 'min_points' => 0]);
        Rank::create(['name' => 'World Champion', 'min_points' => 200]);
        User::factory()->create(['username' => 'Leader', 'points' => 300, 'team_id' => $team->id]);
        $user = User::factory()->create(['username' => 'Current', 'points' => 200, 'team_id' => $team->id]);
        User::factory()->create(['username' => 'Tied', 'points' => 200, 'team_id' => $team->id]);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('driver.position', 2)
            ->where('driver.rank', 'World Champion')
            ->where('leaderboard.1.position', 2)
            ->where('leaderboard.2.position', 2)
            ->where('constructors.0.points', 700));
    }

    public function test_profile_updates_identity_and_shows_completed_history(): void
    {
        $team = Team::create(['name' => 'Williams', 'color' => '#37bedd']);
        $user = User::factory()->create(['team_id' => $team->id]);
        QuizAttempt::create([
            'user_id' => $user->id,
            'difficulty' => 'easy',
            'status' => QuizAttemptStatus::Completed,
            'question_count' => 10,
            'correct_answers' => 8,
            'accuracy' => 80,
            'points_awarded' => 80,
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);

        $this->actingAs($user)->get('/profile')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('stats.totalRaces', 1)
            ->where('stats.averageAccuracy', 80)
            ->has('history', 1));

        $this->actingAs($user)->patch('/profile', [
            'username' => 'UpdatedDriver',
            'email' => 'updated@example.com',
            'team_id' => $team->id,
        ])->assertRedirect('/profile');

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame('UpdatedDriver', $user->fresh()->username);
    }
}
