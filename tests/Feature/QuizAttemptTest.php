<?php

namespace Tests\Feature;

use App\Enums\QuizAttemptStatus;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_an_attempt_returns_unique_questions_without_answers(): void
    {
        $user = User::factory()->create();
        $this->createQuestions('medium', 12);

        $response = $this->actingAs($user)->postJson('/quiz/attempts', ['difficulty' => 'medium']);

        $response->assertCreated()
            ->assertJsonPath('attempt.difficulty', 'medium')
            ->assertJsonCount(10, 'questions')
            ->assertJsonMissingPath('questions.0.correct_option');
        $ids = collect($response->json('questions'))->pluck('id');
        $this->assertCount(10, $ids->unique());
    }

    public function test_insufficient_questions_prevents_an_attempt(): void
    {
        $user = User::factory()->create();
        $this->createQuestions('hard', 9);

        $this->actingAs($user)
            ->postJson('/quiz/attempts', ['difficulty' => 'hard'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('difficulty');
    }

    public function test_answers_lock_and_attempt_ownership_is_enforced(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $this->createQuestions('easy', 10);
        $start = $this->actingAs($user)->postJson('/quiz/attempts', ['difficulty' => 'easy']);
        $attemptId = $start->json('attempt.id');
        $questionId = $start->json('questions.0.id');

        $this->actingAs($stranger)
            ->postJson("/quiz/attempts/{$attemptId}/answers", ['question_id' => $questionId, 'option' => 'a'])
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson("/quiz/attempts/{$attemptId}/answers", ['question_id' => $questionId, 'option' => 'a'])
            ->assertOk()
            ->assertJsonPath('correct', true)
            ->assertJsonPath('correct_option', 'a');

        $this->actingAs($user)
            ->postJson("/quiz/attempts/{$attemptId}/answers", ['question_id' => $questionId, 'option' => 'a'])
            ->assertOk();

        $this->actingAs($user)
            ->postJson("/quiz/attempts/{$attemptId}/answers", ['question_id' => $questionId, 'option' => 'b'])
            ->assertConflict();
    }

    public function test_completion_is_server_scored_transactional_and_idempotent(): void
    {
        $user = User::factory()->create(['points' => 5]);
        $this->createQuestions('hard', 10);
        $start = $this->actingAs($user)->postJson('/quiz/attempts', ['difficulty' => 'hard']);
        $attemptId = $start->json('attempt.id');

        foreach ($start->json('questions') as $index => $question) {
            $this->actingAs($user)->postJson("/quiz/attempts/{$attemptId}/answers", [
                'question_id' => $question['id'],
                'option' => $index < 7 ? 'a' : 'b',
            ])->assertOk();
        }

        $response = $this->actingAs($user)->postJson("/quiz/attempts/{$attemptId}/complete");
        $response->assertOk()
            ->assertJson([
                'correct_answers' => 7,
                'total_questions' => 10,
                'accuracy' => 70,
                'points_awarded' => 210,
                'total_points' => 215,
            ]);

        $this->actingAs($user)->postJson("/quiz/attempts/{$attemptId}/complete")
            ->assertOk()
            ->assertJsonPath('total_points', 215);
        $this->assertSame(215, $user->fresh()->points);
    }

    public function test_an_incomplete_attempt_cannot_be_completed(): void
    {
        $user = User::factory()->create();
        $this->createQuestions('easy', 10);
        $attemptId = $this->actingAs($user)
            ->postJson('/quiz/attempts', ['difficulty' => 'easy'])
            ->json('attempt.id');

        $this->actingAs($user)
            ->postJson("/quiz/attempts/{$attemptId}/complete")
            ->assertConflict();
        $this->assertSame(0, $user->fresh()->points);
    }

    public function test_a_new_attempt_abandons_the_previous_unfinished_attempt(): void
    {
        $user = User::factory()->create();
        $this->createQuestions('medium', 10);

        $firstId = $this->actingAs($user)->postJson('/quiz/attempts', ['difficulty' => 'medium'])->json('attempt.id');
        $this->actingAs($user)->postJson('/quiz/attempts', ['difficulty' => 'medium'])->assertCreated();

        $this->assertSame(QuizAttemptStatus::Abandoned, QuizAttempt::findOrFail($firstId)->status);
    }

    private function createQuestions(string $difficulty, int $count): void
    {
        foreach (range(1, $count) as $number) {
            Question::create([
                'prompt' => "Question {$number}?",
                'options' => ['a' => 'Correct', 'b' => 'Wrong B', 'c' => 'Wrong C', 'd' => 'Wrong D'],
                'correct_option' => 'a',
                'difficulty' => $difficulty,
                'is_active' => true,
            ]);
        }
    }
}
