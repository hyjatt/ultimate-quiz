<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_player_can_register_with_a_team(): void
    {
        $team = Team::create(['name' => 'McLaren', 'color' => '#ff8700']);

        $response = $this->post('/register', [
            'username' => 'ApexDriver',
            'email' => 'apex@example.com',
            'team_id' => $team->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'username' => 'ApexDriver',
            'role' => UserRole::Player->value,
            'team_id' => $team->id,
            'points' => 0,
        ]);
    }

    public function test_a_player_can_log_in_with_username_or_email(): void
    {
        $user = User::factory()->create([
            'username' => 'GridMaster',
            'email' => 'grid@example.com',
        ]);

        $this->post('/login', ['login' => 'GridMaster', 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->post('/login', ['login' => 'grid@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_an_admin_role_cannot_enter_player_routes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $_) {
            $this->post('/login', ['login' => $user->email, 'password' => 'wrong-password']);
        }

        $this->post('/login', ['login' => $user->email, 'password' => 'wrong-password'])
            ->assertTooManyRequests();
    }

    public function test_a_player_can_change_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_email_verification_and_password_reset_are_available(): void
    {
        Event::fake();
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $verification = \URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($verification)->assertRedirect('/dashboard?verified=1');
        Event::assertDispatched(Verified::class);

        $this->post('/logout');
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_verification_email_uses_the_f1quiz_theme(): void
    {
        $user = User::factory()->unverified()->create(['username' => 'PoleSitter']);
        $mail = (new VerifyEmail)->toMail($user);

        $this->assertSame('Confirm your F1Quiz race licence', $mail->subject);
        $this->assertSame('emails.verify-email', $mail->view['html']);
        $this->assertSame('emails.verify-email-text', $mail->view['text']);
        $this->assertSame('PoleSitter', $mail->viewData['username']);
        $this->assertStringContainsString('/email/verify/', $mail->viewData['url']);
    }
}
