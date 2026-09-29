<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlayerRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'status' => ['nullable', 'in:active,suspended'],
        ]);

        $players = User::query()
            ->with('team:id,name,color')
            ->withCount('quizAttempts')
            ->where('role', UserRole::Player->value)
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request): void {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($nested) => $nested->where('username', 'like', $search)->orWhere('email', 'like', $search));
            })
            ->when($request->filled('team_id'), fn ($query) => $query->where('team_id', $request->integer('team_id')))
            ->when($request->input('status') === 'active', fn ($query) => $query->whereNull('suspended_at'))
            ->when($request->input('status') === 'suspended', fn ($query) => $query->whereNotNull('suspended_at'))
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/players/index', [
            'players' => $players,
            'teams' => Team::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'team_id', 'status']),
        ]);
    }

    public function show(User $player): Response
    {
        $this->ensurePlayer($player);
        $player->load('team:id,name,color');

        return Inertia::render('admin/players/show', [
            'player' => $player->only(['id', 'username', 'email', 'email_verified_at', 'points', 'suspended_at', 'suspension_reason', 'created_at']) + [
                'team' => $player->team,
            ],
            'attempts' => $player->quizAttempts()->latest()->paginate(15),
        ]);
    }

    public function edit(User $player): Response
    {
        $this->ensurePlayer($player);

        return Inertia::render('admin/players/edit', [
            'player' => $player->only(['id', 'username', 'email', 'team_id']),
            'teams' => Team::query()->where('is_active', true)->orWhereKey($player->team_id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(PlayerRequest $request, User $player, AuditLogger $audit): RedirectResponse
    {
        $this->ensurePlayer($player);
        $before = $player->getAttributes();
        $attributes = $request->validated();

        if ($attributes['email'] !== $player->email) {
            $attributes['email_verified_at'] = null;
        }

        $player->update($attributes);
        $audit->record('player.updated', $player, $before, $player->fresh()->getAttributes());

        return redirect()->route('admin.players.show', $player)->with('status', 'Player updated.');
    }

    public function suspend(Request $request, User $player, AuditLogger $audit): RedirectResponse
    {
        $this->ensurePlayer($player);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $before = $player->getAttributes();
        $player->update(['suspended_at' => now(), 'suspension_reason' => $validated['reason']]);
        $audit->record('player.suspended', $player, $before, $player->fresh()->getAttributes());

        return back()->with('status', 'Player suspended.');
    }

    public function reactivate(User $player, AuditLogger $audit): RedirectResponse
    {
        $this->ensurePlayer($player);
        $before = $player->getAttributes();
        $player->update(['suspended_at' => null, 'suspension_reason' => null]);
        $audit->record('player.reactivated', $player, $before, $player->fresh()->getAttributes());

        return back()->with('status', 'Player reactivated.');
    }

    public function verify(User $player, AuditLogger $audit): RedirectResponse
    {
        $this->ensurePlayer($player);
        $before = $player->getAttributes();
        $player->markEmailAsVerified();
        $audit->record('player.verified', $player, $before, $player->fresh()->getAttributes());

        return back()->with('status', 'Player email marked as verified.');
    }

    private function ensurePlayer(User $player): void
    {
        abort_unless($player->role === UserRole::Player, 404);
    }
}
