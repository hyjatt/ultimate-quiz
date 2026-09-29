<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionExportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'status' => ['nullable', 'in:active,archived'],
        ]);

        $questions = Question::query()
            ->when($request->string('search')->isNotEmpty(), fn ($query) => $query->where('prompt', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('difficulty'), fn ($query) => $query->where('difficulty', $request->string('difficulty')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderBy('id');

        return response()->streamDownload(function () use ($questions): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['prompt', 'difficulty', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'is_active']);
            $questions->lazyById()->each(function (Question $question) use ($output): void {
                fputcsv($output, [
                    $question->prompt,
                    $question->difficulty->value,
                    $question->options['a'],
                    $question->options['b'],
                    $question->options['c'],
                    $question->options['d'],
                    $question->correct_option,
                    $question->is_active ? '1' : '0',
                ]);
            });
            fclose($output);
        }, 'f1quiz-questions-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
