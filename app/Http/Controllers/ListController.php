<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileList;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListController extends Controller
{
    public function index(): Response
    {
        $lists = FileList::query()
            ->select(['id', 'name', 'color', 'requirements', 'created_at'])
            ->withCount('files')
            ->latest()
            ->get()
            ->map(function ($list) {
                $requirements = $list->requirements ?? [];

                if (empty($requirements) || $list->files_count === 0) {
                    $list->complete_count = 0;
                    $list->completion_rate = 0;
                    return $list;
                }

                $files = File::where('list_id', $list->id)->get();
                $completeCount = 0;

                foreach ($files as $file) {
                    $attachments = $file->attachments ?? [];
                    $missing = array_filter($requirements, fn($req) => !isset($attachments[$req]));
                    if (empty($missing)) {
                        $completeCount++;
                    }
                }

                $list->complete_count  = $completeCount;
                $list->completion_rate = round(($completeCount / $list->files_count) * 100);

                return $list;
            });

        return Inertia::render('lists/index', [
            'lists' => $lists,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'color'        => ['nullable', 'string', 'max:32'],
            'requirements' => ['nullable', 'array'],
        ]);

        $list = FileList::create($validated);

        $reqs = !empty($list->requirements) ? implode(', ', $list->requirements) : 'None';

        ActivityLogger::log(
            'created',
            'employment-types',
            "Created employment type \"{$list->name}\" | Color: " . ($list->color ?? 'None') .
            " | Requirements: {$reqs}"
        );

        return redirect()->route('lists.index');
    }

    public function update(Request $request, FileList $list): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'color'        => ['nullable', 'string', 'max:32'],
            'requirements' => ['nullable', 'array'],
        ]);

        $before = [
            'name'         => $list->name,
            'color'        => $list->color ?? 'None',
            'requirements' => !empty($list->requirements) ? implode(', ', $list->requirements) : 'None',
        ];

        $list->update($validated);

        $after = [
            'name'         => $validated['name'],
            'color'        => $validated['color'] ?? 'None',
            'requirements' => !empty($validated['requirements']) ? implode(', ', $validated['requirements']) : 'None',
        ];

        ActivityLogger::logChanges('updated', 'employment-types', "Updated employment type \"{$list->name}\"", $before, $after);

        return redirect()->route('lists.index');
    }

    public function destroy(FileList $list): RedirectResponse
    {
        $reqs = !empty($list->requirements) ? implode(', ', $list->requirements) : 'None';

        ActivityLogger::log(
            'deleted',
            'employment-types',
            "Deleted employment type \"{$list->name}\" | Color: " . ($list->color ?? 'None') .
            " | Requirements: {$reqs}"
        );

        $list->delete();

        return redirect()->route('lists.index');
    }
}
