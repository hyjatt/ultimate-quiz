<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RankRequest;
use App\Models\Rank;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('admin/ranks/index', [
            'ranks' => Rank::query()->orderBy('min_points')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(RankRequest $request, AuditLogger $audit): RedirectResponse
    {
        $rank = Rank::create($request->validated());
        $audit->record('rank.created', $rank, null, $rank->getAttributes());

        return back()->with('status', 'Rank created.');
    }

    /**
     * Display the specified resource.
     */
    public function update(RankRequest $request, Rank $rank, AuditLogger $audit): RedirectResponse
    {
        abort_if($rank->min_points === 0 && $request->integer('min_points') !== 0, 422, 'The baseline rank must remain at zero points.');

        $before = $rank->getAttributes();
        $rank->update($request->validated());
        $audit->record('rank.updated', $rank, $before, $rank->fresh()->getAttributes());

        return back()->with('status', 'Rank updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rank $rank, AuditLogger $audit): RedirectResponse
    {
        abort_if($rank->min_points === 0 || Rank::query()->count() <= 1, 409, 'The baseline rank cannot be deleted.');
        $before = $rank->getAttributes();
        $audit->record('rank.deleted', $rank, $before);
        $rank->delete();

        return back()->with('status', 'Rank deleted.');
    }
}
