<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileList;
use App\Models\PhysicalLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
{
    $query = File::query()->with([
        'list:id,name,color,requirements',
        'physical_location'
    ]);

    // 1. Search filter
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('fullname', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }

    // 2. Employment Type filter
    if ($request->filled('list_id')) {
        $query->where('list_id', $request->list_id);
    }

    /* |--------------------------------------------------------------------------
    | Default Alphabetical Sorting
    |--------------------------------------------------------------------------
    | We check the 'sort' parameter. If it's missing or null,
    | we force it to 'asc' (A-Z).
    */
    $sortDirection = $request->get('sort', 'asc');

    // Validate sort direction to prevent SQL injection or errors
    if (!in_array($sortDirection, ['asc', 'desc'])) {
        $sortDirection = 'asc';
    }

    $query->orderBy('fullname', $sortDirection);

    // Paginate and preserve the 'sort' parameter in the URL links
    $files = $query->paginate(10)->withQueryString();

    $lists = FileList::select(['id', 'name', 'color', 'requirements'])->get();
    $physical_locations = PhysicalLocation::select(['id', 'name', 'color', 'storage_paths'])->get();

    return Inertia::render('files/index', [
        'files' => $files,
        'lists' => $lists,
        'physical_locations' => $physical_locations,
        'filters' => [
            'search' => $request->search,
            'list_id' => $request->list_id,
            'sort' => $sortDirection, // This ensures the Vue dropdown stays on 'asc' by default
        ],
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_id' => ['required', 'exists:lists,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'],
            'physical_path' => ['nullable', 'string', 'max:255'],
        ]);

        File::create($validated);

        return redirect()->back()->with('success', 'Record created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_id' => ['required', 'exists:lists,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'],
            'physical_path' => ['nullable', 'string', 'max:255'],
            'new_attachments' => ['nullable', 'array'],
            'new_attachments.*' => ['file', 'mimes:pdf,jpg,png,docx', 'max:5120'],
            'delete_attachments' => ['nullable', 'array'],
        ]);

        $attachments = $file->attachments ?? [];

        // 1. Handle Deletions of specific documents
        if ($request->filled('delete_attachments')) {
            foreach ($request->delete_attachments as $reqName) {
                if (isset($attachments[$reqName])) {
                    Storage::disk('public')->delete($attachments[$reqName]);
                    unset($attachments[$reqName]);
                }
            }
        }

        // 2. Handle New File Uploads
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $reqName => $uploadedFile) {
                // Remove old version if it exists before replacing
                if (isset($attachments[$reqName])) {
                    Storage::disk('public')->delete($attachments[$reqName]);
                }
                $path = $uploadedFile->store('attachments', 'public');
                $attachments[$reqName] = $path;
            }
        }

        // 3. Update the database record
        $file->update([
            'fullname' => $validated['fullname'],
            'description' => $validated['description'],
            'list_id' => $validated['list_id'],
            'physical_location_id' => $validated['physical_location_id'],
            'physical_path' => $validated['physical_path'],
            'attachments' => $attachments,
        ]);

        return redirect()->back()->with('success', 'Changes saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file): RedirectResponse
    {
        // Delete all physical files associated with this record before deleting the DB entry
        if ($file->attachments) {
            foreach ($file->attachments as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $file->delete();
        return redirect()->back()->with('success', 'Record deleted.');
    }
}
