<?php

namespace App\Services;

use App\Enums\QuizDifficulty;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class QuestionCsvImporter
{
    private const HEADERS = [
        'prompt',
        'difficulty',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'is_active',
    ];

    /** @return array{rows: array<int, array<string, mixed>>, can_apply: bool, new_count: int, duplicate_count: int, invalid_count: int} */
    public function preview(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new RuntimeException('The CSV file could not be read.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('The CSV file is empty.');
        }

        $headers = array_map(fn (string $header): string => Str::lower(trim($header)), $headers);
        if ($headers !== self::HEADERS) {
            fclose($handle);
            throw new RuntimeException('The CSV header must exactly match the provided template.');
        }

        $existing = Question::query()
            ->get(['prompt', 'difficulty'])
            ->mapWithKeys(fn (Question $question): array => [$this->duplicateKey($question->prompt, $question->difficulty->value) => true]);
        $seen = collect();
        $rows = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if ($line > 1001) {
                fclose($handle);
                throw new RuntimeException('Question imports are limited to 1,000 rows.');
            }

            $data = count($values) === count(self::HEADERS) ? array_combine(self::HEADERS, $values) : [];
            $validator = Validator::make($data, [
                'prompt' => ['required', 'string', 'max:2000'],
                'difficulty' => ['required', Rule::enum(QuizDifficulty::class)],
                'option_a' => ['required', 'string', 'max:500'],
                'option_b' => ['required', 'string', 'max:500', 'different:option_a'],
                'option_c' => ['required', 'string', 'max:500', 'different:option_a', 'different:option_b'],
                'option_d' => ['required', 'string', 'max:500', 'different:option_a', 'different:option_b', 'different:option_c'],
                'correct_option' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
                'is_active' => ['required', Rule::in(['1', '0', 'true', 'false'])],
            ]);

            if ($validator->fails()) {
                $rows[] = ['line' => $line, 'status' => 'invalid', 'data' => $data, 'errors' => $validator->errors()->all()];

                continue;
            }

            $validated = $validator->validated();
            $key = $this->duplicateKey($validated['prompt'], $validated['difficulty']);
            $duplicate = $existing->has($key) || $seen->has($key);
            $seen->put($key, true);
            $rows[] = [
                'line' => $line,
                'status' => $duplicate ? 'duplicate' : 'new',
                'data' => $validated,
                'errors' => [],
            ];
        }

        fclose($handle);
        $collection = collect($rows);

        return [
            'rows' => $rows,
            'can_apply' => $collection->where('status', 'invalid')->isEmpty() && $collection->where('status', 'new')->isNotEmpty(),
            'new_count' => $collection->where('status', 'new')->count(),
            'duplicate_count' => $collection->where('status', 'duplicate')->count(),
            'invalid_count' => $collection->where('status', 'invalid')->count(),
        ];
    }

    /** @param array<int, array<string, mixed>> $rows */
    public function import(array $rows): int
    {
        $newRows = collect($rows)->where('status', 'new');

        foreach ($newRows as $row) {
            $data = $row['data'];
            Question::create([
                'prompt' => $data['prompt'],
                'difficulty' => $data['difficulty'],
                'options' => [
                    'a' => $data['option_a'],
                    'b' => $data['option_b'],
                    'c' => $data['option_c'],
                    'd' => $data['option_d'],
                ],
                'correct_option' => $data['correct_option'],
                'is_active' => in_array($data['is_active'], ['1', 'true'], true),
            ]);
        }

        return $newRows->count();
    }

    private function duplicateKey(string $prompt, string $difficulty): string
    {
        return Str::lower(trim($difficulty)).'|'.Str::lower(trim($prompt));
    }
}
