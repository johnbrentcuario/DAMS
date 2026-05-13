<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileList;
use App\Models\PhysicalLocation;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\SimpleExcel\SimpleExcelWriter;

class FileController extends Controller
{
    public function index(Request $request): Response
    {
        $query = File::query()->with([
            'list:id,name,color,requirements',
            'physical_location'
        ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        if ($request->filled('missing_requirement')) {
            $requirement = $request->missing_requirement;
            $query->where(function ($sub) use ($requirement) {
                $sub->whereRaw("JSON_CONTAINS_PATH(attachments, 'one', '$.\"$requirement\"') = 0")
                    ->orWhereNull('attachments');
            });
        }

        $sortDirection = $request->get('sort', 'asc');
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        $query->orderBy('fullname', $sortDirection);

        $files              = $query->paginate(10)->withQueryString();
        $lists              = FileList::select(['id', 'name', 'color', 'requirements'])->get();
        $physical_locations = PhysicalLocation::select(['id', 'name', 'color', 'storage_paths'])->get();

        return Inertia::render('files/index', [
            'files'              => $files,
            'lists'              => $lists,
            'physical_locations' => $physical_locations,
            'filters'            => [
                'search'              => $request->search,
                'list_id'             => $request->list_id,
                'missing_requirement' => $request->missing_requirement,
                'sort'                => $sortDirection,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fullname'             => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'list_id'              => ['required', 'exists:lists,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'],
            'physical_path'        => ['nullable', 'string', 'max:255'],
        ]);

        $file = File::create($validated);

        $list     = FileList::find($validated['list_id']);
        $location = $validated['physical_location_id']
            ? PhysicalLocation::find($validated['physical_location_id'])
            : null;

        ActivityLogger::log(
            'created',
            'files',
            "Created folder \"{$file->fullname}\" | Employment Type: " . ($list?->name ?? 'None') .
            " | Location: " . ($location?->name ?? 'None') .
            ($validated['physical_path'] ? " | Path: {$validated['physical_path']}" : '')
        );

        return redirect()->back()->with('success', 'Record created successfully.');
    }

    public function update(Request $request, File $file): RedirectResponse
    {
        $validated = $request->validate([
            'fullname'             => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'list_id'              => ['required', 'exists:lists,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'],
            'physical_path'        => ['nullable', 'string', 'max:255'],
            'new_attachments'      => ['nullable', 'array'],
            'new_attachments.*'    => ['file', 'mimes:pdf,jpg,png,docx', 'max:5120'],
            'delete_attachments'   => ['nullable', 'array'],
            'na_attachments'       => ['nullable', 'array'],
            'na_attachments.*'     => ['string'],
        ]);

        $before = [
            'name'        => $file->fullname,
            'description' => $file->description ?? 'empty',
            'list'        => $file->list?->name ?? 'None',
            'location'    => $file->physical_location?->name ?? 'None',
            'path'        => $file->physical_path ?? 'empty',
        ];

        $attachments   = $file->attachments ?? [];
        $deletedFiles  = [];
        $uploadedFiles = [];
        $naFiles       = [];

        // ── 1. Delete removed attachments ────────────────────────────────────
        foreach ($request->input('delete_attachments', []) as $reqName) {
            if (isset($attachments[$reqName]) && $attachments[$reqName] !== '__NA__') {
                Storage::disk('public')->delete($attachments[$reqName]);
            }
            unset($attachments[$reqName]);
            $deletedFiles[] = $reqName;
        }

        // ── 2. Mark N/A ───────────────────────────────────────────────────────
        //    Store '__NA__' sentinel. If a real file existed, delete it first.
        foreach ($request->input('na_attachments', []) as $reqName) {
            if (isset($attachments[$reqName]) && $attachments[$reqName] !== '__NA__') {
                Storage::disk('public')->delete($attachments[$reqName]);
            }
            $attachments[$reqName] = '__NA__';
            $naFiles[] = $reqName;
        }

        // ── 3. Upload new / replacement files ────────────────────────────────
        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $reqName => $uploadedFile) {
                // Uploading a real file always overrides N/A or an old file
                if (isset($attachments[$reqName]) && $attachments[$reqName] !== '__NA__') {
                    Storage::disk('public')->delete($attachments[$reqName]);
                }
                $path = $uploadedFile->store('attachments', 'public');
                $attachments[$reqName] = $path;
                $uploadedFiles[] = $reqName;
            }
        }

        $newList     = FileList::find($validated['list_id']);
        $newLocation = $validated['physical_location_id']
            ? PhysicalLocation::find($validated['physical_location_id'])
            : null;

        $after = [
            'name'        => $validated['fullname'],
            'description' => $validated['description'] ?? 'empty',
            'list'        => $newList?->name ?? 'None',
            'location'    => $newLocation?->name ?? 'None',
            'path'        => $validated['physical_path'] ?? 'empty',
        ];

        $file->update([
            'fullname'             => $validated['fullname'],
            'description'          => $validated['description'],
            'list_id'              => $validated['list_id'],
            'physical_location_id' => $validated['physical_location_id'],
            'physical_path'        => $validated['physical_path'],
            'attachments'          => $attachments,
        ]);

        // ── Build activity log extras ─────────────────────────────────────────
        $extras = [];
        if (!empty($uploadedFiles)) {
            $extras[] = 'Uploaded: '  . implode(', ', $uploadedFiles);
        }
        if (!empty($deletedFiles)) {
            $extras[] = 'Removed: '   . implode(', ', $deletedFiles);
        }
        if (!empty($naFiles)) {
            $extras[] = 'Marked N/A: ' . implode(', ', $naFiles);
        }

        ActivityLogger::logChanges(
            'updated',
            'files',
            "Updated folder \"{$file->fullname}\"" . (!empty($extras) ? ' | ' . implode(' | ', $extras) : ''),
            $before,
            $after
        );

        return redirect()->back()->with('success', 'Changes saved successfully.');
    }

    public function destroy(File $file): RedirectResponse
    {
        $attachmentList = $file->attachments ? implode(', ', array_keys($file->attachments)) : 'None';

        if ($file->attachments) {
            foreach ($file->attachments as $path) {
                // Skip the N/A sentinel — nothing to delete from storage
                if ($path !== '__NA__') {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        ActivityLogger::log(
            'deleted',
            'files',
            "Deleted folder \"{$file->fullname}\" | Employment Type: " . ($file->list?->name ?? 'None') .
            " | Location: " . ($file->physical_location?->name ?? 'None') .
            " | Attachments removed: {$attachmentList}"
        );

        $file->delete();

        return redirect()->back()->with('success', 'Record deleted.');
    }

    // ─── Bulk Actions ─────────────────────────────────────────────────────────

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:files,id'],
        ]);

        $files = File::whereIn('id', $validated['ids'])->get();

        foreach ($files as $file) {
            if ($file->attachments) {
                foreach ($file->attachments as $path) {
                    if ($path !== '__NA__') {
                        Storage::disk('public')->delete($path);
                    }
                }
            }
            ActivityLogger::log(
                'deleted',
                'files',
                "Bulk deleted folder \"{$file->fullname}\" | Employment Type: " . ($file->list?->name ?? 'None') .
                " | Location: " . ($file->physical_location?->name ?? 'None')
            );
            $file->delete();
        }

        return redirect()->back()->with('success', count($validated['ids']) . ' folder(s) deleted successfully.');
    }

    public function bulkMove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids'                  => ['required', 'array'],
            'ids.*'                => ['exists:files,id'],
            'physical_location_id' => ['nullable', 'exists:physical_locations,id'],
            'physical_path'        => ['nullable', 'string'],
        ]);

        $location = $validated['physical_location_id']
            ? PhysicalLocation::find($validated['physical_location_id'])
            : null;

        File::whereIn('id', $validated['ids'])->update([
            'physical_location_id' => $validated['physical_location_id'] ?: null,
            'physical_path'        => $validated['physical_path'] ?: null,
        ]);

        ActivityLogger::log(
            'updated',
            'files',
            'Bulk moved ' . count($validated['ids']) . ' folder(s) to location "' . ($location?->name ?? 'None') . '"' .
            ($validated['physical_path'] ? ' | Path: ' . $validated['physical_path'] : '')
        );

        return redirect()->back()->with('success', count($validated['ids']) . ' folder(s) moved successfully.');
    }

    public function bulkChangeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids'     => ['required', 'array'],
            'ids.*'   => ['exists:files,id'],
            'list_id' => ['required', 'exists:lists,id'],
        ]);

        $list = FileList::find($validated['list_id']);

        File::whereIn('id', $validated['ids'])->update([
            'list_id' => $validated['list_id'],
        ]);

        ActivityLogger::log(
            'updated',
            'files',
            'Bulk changed employment type to "' . ($list?->name ?? 'N/A') . '" for ' . count($validated['ids']) . ' folder(s)'
        );

        return redirect()->back()->with('success', count($validated['ids']) . ' folder(s) updated successfully.');
    }

    public function bulkExport(Request $request)
    {
        $ids    = explode(',', $request->ids ?? '');
        $format = $request->format ?? 'pdf';

        $files = File::with('list', 'physical_location')
            ->whereIn('id', $ids)
            ->get();

        $headings = ['Full Name', 'Employment Type', 'Location', 'Physical Path', 'Attachments', 'Required Docs'];

        $rows = $files->map(fn($file) => [
            'Full Name'       => $file->fullname,
            'Employment Type' => $file->list?->name ?? 'N/A',
            'Location'        => $file->physical_location?->name ?? 'N/A',
            'Physical Path'   => $file->physical_path ?? 'N/A',
            'Attachments'     => count(array_filter($file->attachments ?? [], fn($v) => $v !== '__NA__')),
            'Required Docs'   => count($file->list?->requirements ?? []),
        ])->toArray();

        if ($format === 'excel') {
            return response()->streamDownload(function () use ($headings, $rows) {
                $writer = SimpleExcelWriter::streamDownload('selected-folders.xlsx');
                $writer->addHeader($headings);
                foreach ($rows as $row) {
                    $writer->addRow($row);
                }
                $writer->toBrowserOutput();
            }, 'selected-folders.xlsx');
        }

        $pdf = Pdf::loadView('reports.default', [
            'title'    => 'Selected Folders Export',
            'headings' => $headings,
            'rows'     => array_map('array_values', $rows),
        ])->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="selected-folders.pdf"',
        ]);
    }
}
