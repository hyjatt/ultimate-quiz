<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdministratorRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdministratorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/administrators/index', [
            'administrators' => User::query()
                ->whereIn('role', [UserRole::Admin->value, UserRole::SuperAdmin->value])
                ->orderBy('username')
                ->get(['id', 'username', 'email', 'role', 'suspended_at', 'suspension_reason', 'created_at']),
        ]);
    }

    public function update(AdministratorRequest $request, User $administrator, AuditLogger $audit): RedirectResponse
    {
        abort_unless($administrator->isAdministrator(), 404);
        $validated = $request->validated();
        $wouldChangeSelf = $administrator->is($request->user())
            && ($validated['role'] !== $administrator->role->value || $validated['suspended']);
        abort_if($wouldChangeSelf, 409, 'You cannot demote or suspend your own account.');

        $removingSuperAdmin = $administrator->role === UserRole::SuperAdmin
            && ($validated['role'] !== UserRole::SuperAdmin->value || $validated['suspended']);
        $activeSuperAdmins = User::query()
            ->where('role', UserRole::SuperAdmin->value)
            ->whereNull('suspended_at')
            ->count();
        abort_if($removingSuperAdmin && $activeSuperAdmins <= 1, 409, 'At least one active superadmin is required.');

        $before = $administrator->getAttributes();
        $administrator->update([
            'role' => $validated['role'],
            'suspended_at' => $validated['suspended'] ? now() : null,
            'suspension_reason' => $validated['suspended'] ? $validated['suspension_reason'] : null,
        ]);
        $audit->record('administrator.updated', $administrator, $before, $administrator->fresh()->getAttributes());

        return back()->with('status', 'Administrator updated.');
    }
}
