<?php

namespace Tests\Feature\Admin;

use App\Enums\QuizAttemptStatus;
use App\Models\AuditLog;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_question_and_records_an_audit_entry(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/questions', $this->questionPayload())->assertRedirect();

        $question = Question::query()->sole();
        $this->assertSame('Who won the race?', $question->prompt);
        $this->assertSame('Driver A', $question->options['a']);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'question.created',
            'subject_id' => $question->id,
        ]);
    }

    public function test_editing_a_used_question_creates_a_revision_and_preserves_history(): void
    {
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create();
        $question = Question::create([
            'prompt' => 'Original prompt?',
            'options' => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
            'correct_option' => 'a',
            'difficulty' => 'easy',
            'is_active' => true,
        ]);
        $attempt = QuizAttempt::create([
            'user_id' => $player->id,
            'difficulty' => 'easy',
            'status' => QuizAttemptStatus::Completed,
            'question_count' => 1,
            'correct_answers' => 1,
            'accuracy' => 100,
            'points_awarded' => 10,
            'started_at' => now(),
            'completed_at' => now(),
        ]);
        $attempt->attemptQuestions()->create(['question_id' => $question->id, 'position' => 1, 'selected_option' => 'a', 'is_correct' => true, 'answered_at' => now()]);

        $this->actingAs($admin)->put("/admin/questions/{$question->id}", [
            ...$this->questionPayload(),
            'prompt' => 'Revised prompt?',
        ])->assertRedirect();

        $question->refresh();
        $revision = Question::query()->where('revises_question_id', $question->id)->sole();
        $this->assertFalse($question->is_active);
        $this->assertSame('Original prompt?', $attempt->attemptQuestions()->firstOrFail()->question->prompt);
        $this->assertSame('Revised prompt?', $revision->prompt);
        $this->assertSame(1, AuditLog::query()->where('action', 'question.revised')->count());
    }

    public function test_used_question_cannot_be_permanently_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create();
        $question = Question::create([
            'prompt' => 'Protected?', 'options' => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
            'correct_option' => 'a', 'difficulty' => 'easy', 'is_active' => false,
        ]);
        $attempt = QuizAttempt::create([
            'user_id' => $player->id, 'difficulty' => 'easy', 'status' => 'started',
            'question_count' => 1, 'started_at' => now(),
        ]);
        $attempt->attemptQuestions()->create(['question_id' => $question->id, 'position' => 1]);

        $this->actingAs($admin)->delete("/admin/questions/{$question->id}")->assertConflict();

        $this->assertModelExists($question);
    }

    /** @return array<string, mixed> */
    private function questionPayload(): array
    {
        return [
            'prompt' => 'Who won the race?', 'difficulty' => 'easy',
            'option_a' => 'Driver A', 'option_b' => 'Driver B',
            'option_c' => 'Driver C', 'option_d' => 'Driver D',
            'correct_option' => 'a', 'is_active' => true,
        ];
    }
}
