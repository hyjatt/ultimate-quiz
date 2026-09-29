<?php

namespace App\Http\Controllers;

use App\Enums\QuizAttemptStatus;
use App\Enums\QuizDifficulty;
use App\Http\Requests\AnswerQuizQuestionRequest;
use App\Http\Requests\StartQuizAttemptRequest;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizAttemptController extends Controller
{
    public function store(StartQuizAttemptRequest $request): JsonResponse
    {
        $difficulty = QuizDifficulty::from($request->validated('difficulty'));
        $questions = Question::query()
            ->where('difficulty', $difficulty->value)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        if ($questions->count() < 10) {
            return response()->json([
                'message' => 'This difficulty needs at least 10 active questions before a race can start.',
                'errors' => ['difficulty' => ['Insufficient active questions.']],
            ], 422);
        }

        $attempt = DB::transaction(function () use ($request, $difficulty, $questions) {
            QuizAttempt::query()
                ->whereBelongsTo($request->user())
                ->where('status', QuizAttemptStatus::Started->value)
                ->update(['status' => QuizAttemptStatus::Abandoned->value]);

            $attempt = QuizAttempt::create([
                'user_id' => $request->user()->id,
                'difficulty' => $difficulty,
                'status' => QuizAttemptStatus::Started,
                'question_count' => 10,
                'started_at' => now(),
            ]);

            $attempt->attemptQuestions()->createMany(
                $questions->values()->map(fn (Question $question, int $index) => [
                    'question_id' => $question->id,
                    'position' => $index + 1,
                ])->all(),
            );

            return $attempt;
        });

        return response()->json([
            'attempt' => [
                'id' => $attempt->id,
                'difficulty' => $difficulty->value,
                'total' => 10,
            ],
            'questions' => $questions->values()->map(fn (Question $question) => [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'options' => collect($question->options)
                    ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
                    ->shuffle()
                    ->values(),
            ]),
        ], 201);
    }

    public function answer(
        AnswerQuizQuestionRequest $request,
        QuizAttempt $attempt,
    ): JsonResponse {
        $this->authorizeAttempt($request, $attempt);

        return DB::transaction(function () use ($request, $attempt) {
            $lockedAttempt = QuizAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($lockedAttempt->status !== QuizAttemptStatus::Started) {
                return response()->json(['message' => 'This quiz attempt is no longer active.'], 409);
            }

            $attemptQuestion = QuizAttemptQuestion::query()
                ->with('question')
                ->where('quiz_attempt_id', $lockedAttempt->id)
                ->where('question_id', $request->integer('question_id'))
                ->lockForUpdate()
                ->first();

            if (! $attemptQuestion) {
                return response()->json(['message' => 'That question is not part of this attempt.'], 422);
            }

            $option = $request->validated('option');
            if ($attemptQuestion->selected_option !== null) {
                if ($attemptQuestion->selected_option !== $option) {
                    return response()->json(['message' => 'An answered question cannot be changed.'], 409);
                }

                return $this->answerResponse($lockedAttempt, $attemptQuestion);
            }

            $attemptQuestion->update([
                'selected_option' => $option,
                'is_correct' => $option === $attemptQuestion->question->correct_option,
                'answered_at' => now(),
            ]);

            return $this->answerResponse($lockedAttempt, $attemptQuestion->fresh('question'));
        });
    }

    public function complete(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);

        return DB::transaction(function () use ($request, $attempt) {
            $lockedAttempt = QuizAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($lockedAttempt->status === QuizAttemptStatus::Completed) {
                return response()->json($this->completionPayload($lockedAttempt, $request->user()->fresh()));
            }

            if ($lockedAttempt->status !== QuizAttemptStatus::Started) {
                return response()->json(['message' => 'This quiz attempt is no longer active.'], 409);
            }

            $answered = $lockedAttempt->attemptQuestions()->whereNotNull('answered_at')->count();
            if ($answered !== $lockedAttempt->question_count) {
                return response()->json(['message' => 'Answer every question before completing the race.'], 409);
            }

            $correct = $lockedAttempt->attemptQuestions()->where('is_correct', true)->count();
            $points = $correct * $lockedAttempt->difficulty->pointsPerAnswer();
            $accuracy = (int) round(($correct / $lockedAttempt->question_count) * 100);

            $lockedAttempt->update([
                'status' => QuizAttemptStatus::Completed,
                'correct_answers' => $correct,
                'accuracy' => $accuracy,
                'points_awarded' => $points,
                'completed_at' => now(),
            ]);

            if ($points > 0) {
                $request->user()->increment('points', $points);
            }

            return response()->json($this->completionPayload($lockedAttempt->fresh(), $request->user()->fresh()));
        });
    }

    private function authorizeAttempt(Request $request, QuizAttempt $attempt): void
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
    }

    private function answerResponse(QuizAttempt $attempt, QuizAttemptQuestion $attemptQuestion): JsonResponse
    {
        return response()->json([
            'question_id' => $attemptQuestion->question_id,
            'correct' => $attemptQuestion->is_correct,
            'correct_option' => $attemptQuestion->question->correct_option,
            'answered' => $attempt->attemptQuestions()->whereNotNull('answered_at')->count(),
            'total' => $attempt->question_count,
        ]);
    }

    private function completionPayload(QuizAttempt $attempt, $user): array
    {
        return [
            'correct_answers' => $attempt->correct_answers,
            'total_questions' => $attempt->question_count,
            'accuracy' => $attempt->accuracy,
            'points_awarded' => $attempt->points_awarded,
            'total_points' => $user->points,
        ];
    }
}
