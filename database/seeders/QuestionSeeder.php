<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        if (Question::query()->exists()) {
            return;
        }

        $questions = json_decode(
            file_get_contents(__DIR__.'/data/questions.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $now = now();
        foreach (array_chunk($questions, 50) as $chunk) {
            Question::query()->insert(array_map(fn (array $question) => [
                ...$question,
                'options' => json_encode($question['options'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }
    }
}
