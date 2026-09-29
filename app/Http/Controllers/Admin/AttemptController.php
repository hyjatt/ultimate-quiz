<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttemptController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'status' => ['nullable', 'in:started,completed,abandoned'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $attempts = QuizAttempt::query()
            ->with('user:id,username,email')
            ->when($request->string('search')->isNotEmpty(), fn ($query) => $query->whereHas('user', function ($userQuery) use ($request): void {
                $search = '%'.$request->string('search').'%';
                $userQuery->where(fn ($nested) => $nested->where('username', 'like', $search)->orWhere('email', 'like', $search));
            }))
            ->when($request->filled('difficulty'), fn ($query) => $query->where('difficulty', $request->string('difficulty')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('started_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('started_at', '<=', $request->date('to')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/attempts/index', [
            'attempts' => $attempts,
            'filters' => $request->only(['search', 'difficulty', 'status', 'from', 'to']),
        ]);
    }

    public function show(QuizAttempt $attempt): Response
    {
        $attempt->load([
            'user:id,username,email',
            'attemptQuestions.question:id,prompt,options,correct_option',
        ]);

        return Inertia::render('admin/attempts/show', ['attempt' => $attempt]);
    }
}
