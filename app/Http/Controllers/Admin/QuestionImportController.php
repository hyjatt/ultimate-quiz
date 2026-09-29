<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\QuestionCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class QuestionImportController extends Controller
{
    public function index(Request $request): Response
    {
        $token = $request->string('token')->toString();

        return Inertia::render('admin/questions/import', [
            'preview' => $token === '' ? null : $request->session()->get("admin.question-import.{$token}"),
            'token' => $token,
        ]);
    }

    public function preview(Request $request, QuestionCsvImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        try {
            $preview = $importer->preview($validated['file']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        $token = Str::random(40);
        $request->session()->put("admin.question-import.{$token}", $preview);

        return redirect()->route('admin.question-import.index', ['token' => $token]);
    }

    public function store(Request $request, QuestionCsvImporter $importer, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate(['token' => ['required', 'string', 'size:40']]);
        $preview = $request->session()->pull("admin.question-import.{$validated['token']}");
        abort_unless(is_array($preview) && ($preview['can_apply'] ?? false), 422, 'This import preview is missing, invalid, or expired.');

        $count = DB::transaction(fn (): int => $importer->import($preview['rows']));
        $audit->record('questions.imported', null, null, ['count' => $count]);

        return redirect()->route('admin.questions.index')->with('status', "{$count} questions imported.");
    }
}
