<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_superadmins_can_manage_administrator_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get('/admin/administrators')->assertForbidden();
        $this->actingAs($superAdmin)->get('/admin/administrators')->assertOk();
    }

    public function test_superadmin_cannot_demote_or_suspend_their_own_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->put("/admin/administrators/{$superAdmin->id}", [
            'role' => UserRole::Admin->value,
            'suspended' => false,
            'suspension_reason' => null,
        ])->assertConflict();

        $this->assertSame(UserRole::SuperAdmin, $superAdmin->fresh()->role);
    }

    public function test_admin_can_safely_moderate_a_player_without_changing_points(): void
    {
        $team = Team::create(['name' => 'Ferrari', 'color' => '#E80020']);
        $admin = User::factory()->admin()->create();
        $player = User::factory()->unverified()->create(['points' => 120, 'team_id' => $team->id]);

        $this->actingAs($admin)->post("/admin/players/{$player->id}/suspension", ['reason' => 'Conduct review'])->assertRedirect();
        $this->actingAs($admin)->post("/admin/players/{$player->id}/verification")->assertRedirect();

        $player->refresh();
        $this->assertNotNull($player->suspended_at);
        $this->assertNotNull($player->email_verified_at);
        $this->assertSame(120, $player->points);
        $this->assertSame(2, AuditLog::query()->where('subject_id', $player->id)->count());
    }
}
