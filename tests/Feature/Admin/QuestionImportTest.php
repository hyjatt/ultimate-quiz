<?php

namespace Tests\Feature\Admin;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_csv_is_previewed_and_applied(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = implode("\n", [
            'prompt,difficulty,option_a,option_b,option_c,option_d,correct_option,is_active',
            'Fastest driver?,easy,Driver A,Driver B,Driver C,Driver D,a,1',
        ]);

        $preview = $this->actingAs($admin)->post('/admin/questions/import/preview', [
            'file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
        ]);
        $preview->assertRedirect();
        parse_str(parse_url($preview->headers->get('Location'), PHP_URL_QUERY) ?? '', $query);

        $this->actingAs($admin)->post('/admin/questions/import', ['token' => $query['token']])->assertRedirect('/admin/questions');

        $this->assertDatabaseHas('questions', ['prompt' => 'Fastest driver?', 'difficulty' => 'easy']);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'questions.imported']);
    }

    public function test_duplicate_questions_are_skipped(): void
    {
        $admin = User::factory()->admin()->create();
        Question::create([
            'prompt' => 'Existing?', 'difficulty' => 'medium',
            'options' => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
            'correct_option' => 'a', 'is_active' => true,
        ]);
        $csv = implode("\n", [
            'prompt,difficulty,option_a,option_b,option_c,option_d,correct_option,is_active',
            'Existing?,medium,A,B,C,D,a,1',
            'New question?,hard,A,B,C,D,d,true',
        ]);

        $preview = $this->actingAs($admin)->post('/admin/questions/import/preview', [
            'file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
        ]);
        parse_str(parse_url($preview->headers->get('Location'), PHP_URL_QUERY) ?? '', $query);
        $this->actingAs($admin)->post('/admin/questions/import', ['token' => $query['token']])->assertRedirect();

        $this->assertDatabaseCount('questions', 2);
        $this->assertSame(1, Question::query()->where('prompt', 'Existing?')->count());
    }

    public function test_invalid_csv_cannot_be_applied(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = implode("\n", [
            'prompt,difficulty,option_a,option_b,option_c,option_d,correct_option,is_active',
            'Broken question?,impossible,A,B,C,D,z,1',
        ]);

        $preview = $this->actingAs($admin)->post('/admin/questions/import/preview', [
            'file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
        ]);
        parse_str(parse_url($preview->headers->get('Location'), PHP_URL_QUERY) ?? '', $query);

        $this->actingAs($admin)->post('/admin/questions/import', ['token' => $query['token']])->assertUnprocessable();
        $this->assertDatabaseCount('questions', 0);
    }
}
