<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamRequest;
use App\Models\Team;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('admin/teams/index', [
            'teams' => Team::query()->withCount('users')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(TeamRequest $request, AuditLogger $audit): RedirectResponse
    {
        $team = Team::create($request->validated());
        $audit->record('team.created', $team, null, $team->getAttributes());

        return back()->with('status', 'Team created.');
    }

    /**
     * Display the specified resource.
     */
    public function update(TeamRequest $request, Team $team, AuditLogger $audit): RedirectResponse
    {
        $before = $team->getAttributes();
        $team->update($request->validated());
        $audit->record($team->is_active ? 'team.updated' : 'team.archived', $team, $before, $team->fresh()->getAttributes());

        return back()->with('status', 'Team updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team, AuditLogger $audit): RedirectResponse
    {
        abort_if($team->users()->exists(), 409, 'Teams with players must be archived.');
        $before = $team->getAttributes();
        $audit->record('team.deleted', $team, $before);
        $team->delete();

        return back()->with('status', 'Unused team deleted.');
    }
}
