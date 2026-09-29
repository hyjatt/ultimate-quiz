<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_and_players_are_forbidden_from_admin_routes(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_administrators_can_open_race_control(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('auth.user.role', UserRole::Admin->value));
    }

    public function test_login_redirects_each_role_to_its_own_application(): void
    {
        $player = User::factory()->create(['email' => 'player@example.com']);
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->post('/login', ['login' => $player->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->post('/logout');
        $this->post('/login', ['login' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
    }

    public function test_suspended_accounts_cannot_log_in_or_continue_an_existing_session(): void
    {
        $admin = User::factory()->admin()->suspended()->create();

        $this->post('/login', ['login' => $admin->email, 'password' => 'password'])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->actingAs($admin)->get('/admin')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_cli_command_creates_a_verified_superadmin(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Username', 'RaceDirector')
            ->expectsQuestion('Email address', 'director@example.com')
            ->expectsChoice('Role', 'superadmin', ['admin', 'superadmin'])
            ->expectsQuestion('Password', 'secure-password')
            ->expectsQuestion('Confirm password', 'secure-password')
            ->assertSuccessful();

        $administrator = User::query()->where('email', 'director@example.com')->sole();
        $this->assertSame(UserRole::SuperAdmin, $administrator->role);
        $this->assertNotNull($administrator->email_verified_at);
    }
}
