<?php

namespace Tests\Feature;

use App\Models\Question;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_content_is_seeded_and_normalized(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('teams', 11);
        $this->assertDatabaseCount('ranks', 6);
        $this->assertDatabaseCount('questions', 93);
        $this->assertSame(['easy' => 20, 'hard' => 20, 'medium' => 53], Question::query()
            ->selectRaw('difficulty, count(*) as aggregate')
            ->groupBy('difficulty')
            ->pluck('aggregate', 'difficulty')
            ->map(fn ($count) => (int) $count)
            ->sortKeys()
            ->all());

        Question::query()->each(function (Question $question) {
            $this->assertSame(['a', 'b', 'c', 'd'], array_keys($question->options));
            $this->assertContains($question->correct_option, ['a', 'b', 'c', 'd']);
        });
    }
}
