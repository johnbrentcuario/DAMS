<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileList;
use App\Models\PhysicalLocation; // NEW: Import the model
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FileController extends Controller
{
    public function index(Request $request): Response
    {
        // Added 'physical_location' to the with() call so the table can show names/colors
        $query = File::query()->with([
            'list:id,name,color,requirements',
            'physical_location' // NEW: Eager load the location
        ]);

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

        // NEW: Fetch all physical locations to populate the dropdowns
        $physical_locations = PhysicalLocation::select(['id', 'name', 'color', 'storage_paths'])->get();

        return Inertia::render('files/index', [
            'files' => $files,
            'lists' => $lists,
            'physical_locations' => $physical_locations, // NEW: Pass this to Vue
            'filters' => $request->only(['search', 'list_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_id' => ['required', 'exists:lists,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'], // NEW
            'physical_path' => ['nullable', 'string', 'max:255'],                   // NEW
        ]);

        File::create($validated);

        return redirect()->back()->with('success', 'Record created successfully.');
    }

    public function update(Request $request, File $file): RedirectResponse
    {
        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'list_id' => ['required', 'exists:lists,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'], // NEW
            'physical_path' => ['nullable', 'string', 'max:255'],                   // NEW
            'new_attachments' => ['nullable', 'array'],
            'new_attachments.*' => ['file', 'mimes:pdf,jpg,png,docx', 'max:5120'],
            'delete_attachments' => ['nullable', 'array'],
        ]);

        $attachments = $file->attachments ?? [];

        // 1. Handle Deletions
        if ($request->filled('delete_attachments')) {
            foreach ($request->delete_attachments as $reqName) {
                if (isset($attachments[$reqName])) {
                    Storage::disk('public')->delete($attachments[$reqName]);
                    unset($attachments[$reqName]);
                }
            }
        }

        // 2. Handle New Uploads
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $reqName => $uploadedFile) {
                if (isset($attachments[$reqName])) {
                    Storage::disk('public')->delete($attachments[$reqName]);
                }
                $path = $uploadedFile->store('attachments', 'public');
                $attachments[$reqName] = $path;
            }
        }

        // 3. Update the database record including physical location
        $file->update([
            'fullname' => $validated['fullname'],
            'description' => $validated['description'],
            'list_id' => $validated['list_id'],
            'physical_location_id' => $validated['physical_location_id'], // NEW
            'physical_path' => $validated['physical_path'],               // NEW
            'attachments' => $attachments,
        ]);

        return redirect()->back()->with('success', 'Changes saved successfully.');
    }

    public function destroy(File $file): RedirectResponse
    {
        if ($file->attachments) {
            foreach ($file->attachments as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $file->delete();
        return redirect()->back()->with('success', 'Record deleted.');
    }
}
