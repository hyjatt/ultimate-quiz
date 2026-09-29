<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $request->validate(['search' => ['nullable', 'string', 'max:200']]);

        $logs = AuditLog::query()
            ->with('actor:id,username,email')
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request): void {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($nested) => $nested
                    ->where('action', 'like', $search)
                    ->orWhereHas('actor', fn ($actor) => $actor->where('username', 'like', $search)));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('admin/audit/index', [
            'logs' => $logs,
            'filters' => $request->only('search'),
        ]);
    }
}
