<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\FileList;
use App\Models\File;
use Inertia\Inertia;
use Inertia\Response;

class FileController extends Controller
{
    public function index(Request $request): Response
    {
        $query = File::query()->with('list:id,name,color');

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // List filter
        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        $files = $query->latest()->paginate(10)->withQueryString();
        $lists = FileList::select(['id', 'name', 'color'])->get();

        return Inertia::render('files/index', [
            'files' => $files,
            'lists' => $lists,
            'filters' => $request->only(['search', 'priority', 'list_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
            'list_id' => ['required', 'exists:lists,id'],
        ]);

        $validated['completed'] = (bool) ($validated['completed'] ?? false);
        $validated['priority'] = $validated['priority'] ?? 'normal';

        File::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, File $file): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'], // ⚠ changed from 'name'
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $validated['completed'] = (bool) ($validated['completed'] ?? $file->completed);
        $validated['priority'] = $validated['priority'] ?? $file->priority;

        $file->update($validated);

        return redirect()->back();
    }

    public function destroy(File $file): RedirectResponse
    {
        $file->delete();
        return redirect()->back();
    }
}
