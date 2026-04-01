<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FileController extends Controller
{
    public function index(Request $request): Response
    {
        $query = File::query()->with(['list' => function ($q) {
            $q->select('id', 'name', 'color', 'requirements');
        }]);

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // List filter
        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        $files = $query->latest()->paginate(10)->withQueryString();

        $lists = FileList::select(['id', 'name', 'color', 'requirements'])->get();

        return Inertia::render('files/index', [
            'files' => $files,
            'lists' => $lists,
            'filters' => $request->only(['search', 'list_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_id' => ['required', 'exists:lists,id'],
        ]);

        File::create($validated);

        return redirect()->back()->with('success', 'Record created successfully.');
    }

    /**
     * The consolidated Update method handling metadata,
     * batch uploads, and batch deletions.
     */
    public function update(Request $request, File $file): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_id' => ['required', 'exists:lists,id'],
            'new_attachments' => ['nullable', 'array'],
            'new_attachments.*' => ['file', 'mimes:pdf,jpg,png,docx', 'max:5120'],
            'delete_attachments' => ['nullable', 'array'],
        ]);

        // Get existing attachments from the JSON column
        $attachments = $file->attachments ?? [];

        // 1. Handle Deletions (Marked in the UI)
        if ($request->filled('delete_attachments')) {
            foreach ($request->delete_attachments as $reqName) {
                if (isset($attachments[$reqName])) {
                    Storage::disk('public')->delete($attachments[$reqName]);
                    unset($attachments[$reqName]);
                }
            }
        }

        // 2. Handle New Uploads (Added in the UI)
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $reqName => $uploadedFile) {
                // If a file already exists for this requirement, delete the old one first
                if (isset($attachments[$reqName])) {
                    Storage::disk('public')->delete($attachments[$reqName]);
                }

                // Store the new file and update the path in the array
                $path = $uploadedFile->store('attachments', 'public');
                $attachments[$reqName] = $path;
            }
        }

        // 3. Update the database record
        $file->update([
            'fullname' => $validated['fullname'],
            'description' => $validated['description'],
            'list_id' => $validated['list_id'],
            'attachments' => $attachments,
        ]);

        return redirect()->back()->with('success', 'Changes saved successfully.');
    }

    public function destroy(File $file): RedirectResponse
    {
        // Delete all associated physical files before deleting the record
        if ($file->attachments) {
            foreach ($file->attachments as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $file->delete();
        return redirect()->back()->with('success', 'Record deleted.');
    }
}
