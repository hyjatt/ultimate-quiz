<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuestionRequest;
use App\Models\Question;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'status' => ['nullable', 'in:active,archived'],
        ]);

        $questions = Question::query()
            ->withCount('attemptQuestions')
            ->when($request->string('search')->isNotEmpty(), fn ($query) => $query->where('prompt', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('difficulty'), fn ($query) => $query->where('difficulty', $request->string('difficulty')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/questions/index', [
            'questions' => $questions,
            'filters' => $request->only(['search', 'difficulty', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/questions/form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuestionRequest $request, AuditLogger $audit): RedirectResponse
    {
        $question = Question::create($this->attributes($request));
        $audit->record('question.created', $question, null, $question->getAttributes());

        return redirect()->route('admin.questions.edit', $question)->with('status', 'Question created.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Question $question): Response
    {
        $question->load('revisedFrom:id,prompt')->loadCount('attemptQuestions');

        return Inertia::render('admin/questions/form', [
            'question' => [
                ...$question->only(['id', 'prompt', 'difficulty', 'correct_option', 'is_active', 'revises_question_id', 'attempt_questions_count']),
                'options' => $question->options,
                'revised_from' => $question->revisedFrom,
            ],
            'revisions' => $question->revisions()->latest('id')->get(['id', 'prompt', 'is_active', 'created_at']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuestionRequest $request, Question $question, AuditLogger $audit): RedirectResponse
    {
        $attributes = $this->attributes($request);
        $before = $question->getAttributes();

        if ($question->attemptQuestions()->exists()) {
            $revision = DB::transaction(function () use ($question, $attributes): Question {
                $question->update(['is_active' => false]);

                return Question::create([
                    ...$attributes,
                    'revises_question_id' => $question->id,
                ]);
            });

            $audit->record('question.revised', $revision, $before, $revision->getAttributes());

            return redirect()->route('admin.questions.edit', $revision)->with('status', 'A new revision was created; the used question was archived.');
        }

        $question->update($attributes);
        $audit->record('question.updated', $question, $before, $question->fresh()->getAttributes());

        return back()->with('status', 'Question updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question, AuditLogger $audit): RedirectResponse
    {
        abort_if($question->attemptQuestions()->exists(), 409, 'Used questions must be archived instead of deleted.');

        $before = $question->getAttributes();
        $audit->record('question.deleted', $question, $before);
        $question->delete();

        return redirect()->route('admin.questions.index')->with('status', 'Unused question deleted.');
    }

    /** @return array<string, mixed> */
    private function attributes(QuestionRequest $request): array
    {
        $validated = $request->validated();

        return [
            'prompt' => $validated['prompt'],
            'difficulty' => $validated['difficulty'],
            'options' => [
                'a' => $validated['option_a'],
                'b' => $validated['option_b'],
                'c' => $validated['option_c'],
                'd' => $validated['option_d'],
            ],
            'correct_option' => $validated['correct_option'],
            'is_active' => $validated['is_active'],
        ];
    }
}
