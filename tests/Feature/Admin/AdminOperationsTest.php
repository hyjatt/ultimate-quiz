<?php

namespace Tests\Feature\Admin;

use App\Models\Rank;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_with_players_is_archived_without_losing_associations(): void
    {
        $admin = User::factory()->admin()->create();
        $team = Team::create(['name' => 'Ferrari', 'color' => '#E80020']);
        $player = User::factory()->create(['team_id' => $team->id]);

        $this->actingAs($admin)->put("/admin/teams/{$team->id}", [
            'name' => 'Ferrari',
            'color' => '#E80020',
            'is_active' => false,
        ])->assertRedirect();

        $this->assertFalse($team->fresh()->is_active);
        $this->assertSame($team->id, $player->fresh()->team_id);
        $this->actingAs($admin)->delete("/admin/teams/{$team->id}")->assertConflict();
    }

    public function test_baseline_rank_cannot_be_moved_or_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $rank = Rank::create(['name' => 'Rookie', 'min_points' => 0]);

        $this->actingAs($admin)->put("/admin/ranks/{$rank->id}", [
            'name' => 'Rookie',
            'min_points' => 10,
        ])->assertUnprocessable();
        $this->actingAs($admin)->delete("/admin/ranks/{$rank->id}")->assertConflict();

        $this->assertSame(0, $rank->fresh()->min_points);
    }
}
