<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\FileList;
use App\Models\PhysicalLocation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'lists'     => FileList::select('id', 'name')->get(),
            'locations' => PhysicalLocation::select('id', 'name')->get(),
        ]);
    }

    public function export(Request $request)
    {
        $type   = $request->type;
        $format = $request->format;

        return match($type) {
            'missing-documents'  => $this->exportMissingDocuments($request, $format),
            'complete-documents' => $this->exportCompleteDocuments($request, $format),
            'folder-summary'     => $this->exportFolderSummary($request, $format),
            'employment-types'   => $this->exportEmploymentTypes($format),
            'locations'          => $this->exportLocations($format),
            'activity-log'       => $this->exportActivityLog($request, $format),
            'users'              => $this->exportUsers($format),
            default              => back()->with('error', 'Invalid report type.'),
        };
    }

    private function exportMissingDocuments(Request $request, string $format)
    {
        $listId   = $request->list_id;
        $headings = ['Full Name', 'Employment Type', 'Location', 'Missing Documents', 'Missing Count'];
        $rows     = [];

        File::with('list', 'physical_location')->get()->each(function ($file) use (&$rows, $listId) {
            $requirements = $file->list?->requirements ?? [];
            $attachments  = $file->attachments ?? [];
            $missing      = array_filter($requirements, fn($req) => !isset($attachments[$req]));

            if (!empty($missing) && (!$listId || $file->list_id == $listId)) {
                $rows[] = [
                    'Full Name'         => $file->fullname,
                    'Employment Type'   => $file->list?->name ?? 'N/A',
                    'Location'          => $file->physical_location?->name ?? 'N/A',
                    'Missing Documents' => implode(', ', $missing),
                    'Missing Count'     => count($missing),
                ];
            }
        });

        if ($format === 'excel') {
            return $this->streamExcel('missing-documents.xlsx', $headings, $rows);
        }

        return $this->streamPdf('Missing Documents Report', $headings, array_map('array_values', $rows), 'missing-documents.pdf');
    }

    private function exportCompleteDocuments(Request $request, string $format)
    {
        $listId   = $request->list_id;
        $headings = ['Full Name', 'Employment Type', 'Location', 'Total Documents'];
        $rows     = [];

        File::with('list', 'physical_location')->get()->each(function ($file) use (&$rows, $listId) {
            $requirements = $file->list?->requirements ?? [];
            $attachments  = $file->attachments ?? [];
            $isComplete   = !empty($requirements) &&
                empty(array_filter($requirements, fn($req) => !isset($attachments[$req])));

            if ($isComplete && (!$listId || $file->list_id == $listId)) {
                $rows[] = [
                    'Full Name'       => $file->fullname,
                    'Employment Type' => $file->list?->name ?? 'N/A',
                    'Location'        => $file->physical_location?->name ?? 'N/A',
                    'Total Documents' => count($requirements),
                ];
            }
        });

        if ($format === 'excel') {
            return $this->streamExcel('complete-documents.xlsx', $headings, $rows);
        }

        return $this->streamPdf('Complete Documents Report', $headings, array_map('array_values', $rows), 'complete-documents.pdf');
    }

    private function exportFolderSummary(Request $request, string $format)
    {
        $listId     = $request->list_id;
        $locationId = $request->location_id;
        $headings   = ['Full Name', 'Employment Type', 'Location', 'Physical Path', 'Attachments', 'Required Docs', 'Created'];

        $rows = File::with('list', 'physical_location')
            ->when($listId, fn($q) => $q->where('list_id', $listId))
            ->when($locationId, fn($q) => $q->where('physical_location_id', $locationId))
            ->get()
            ->map(fn($file) => [
                'Full Name'       => $file->fullname,
                'Employment Type' => $file->list?->name ?? 'N/A',
                'Location'        => $file->physical_location?->name ?? 'N/A',
                'Physical Path'   => $file->physical_path ?? 'N/A',
                'Attachments'     => count($file->attachments ?? []),
                'Required Docs'   => count($file->list?->requirements ?? []),
                'Created'         => $file->created_at->format('M d, Y'),
            ])->toArray();

        if ($format === 'excel') {
            return $this->streamExcel('folder-summary.xlsx', $headings, $rows);
        }

        return $this->streamPdf('Folder Summary Report', $headings, array_map('array_values', $rows), 'folder-summary.pdf');
    }

    private function exportEmploymentTypes(string $format)
    {
        $headings = ['Employment Type', 'Total Folders', 'Requirements', 'Requirement Count'];

        $rows = FileList::withCount('files')->get()->map(fn($list) => [
            'Employment Type'   => $list->name,
            'Total Folders'     => $list->files_count,
            'Requirements'      => implode(', ', $list->requirements ?? []),
            'Requirement Count' => count($list->requirements ?? []),
        ])->toArray();

        if ($format === 'excel') {
            return $this->streamExcel('employment-types.xlsx', $headings, $rows);
        }

        return $this->streamPdf('Employment Type Report', $headings, array_map('array_values', $rows), 'employment-types.pdf');
    }

    private function exportLocations(string $format)
    {
        $headings = ['Location', 'Total Folders', 'Storage Paths'];

        $rows = PhysicalLocation::withCount('files')->get()->map(fn($loc) => [
            'Location'      => $loc->name,
            'Total Folders' => $loc->files_count,
            'Storage Paths' => implode(', ', $loc->storage_paths ?? []),
        ])->toArray();

        if ($format === 'excel') {
            return $this->streamExcel('locations.xlsx', $headings, $rows);
        }

        return $this->streamPdf('Location Report', $headings, array_map('array_values', $rows), 'locations.pdf');
    }

    private function exportActivityLog(Request $request, string $format)
    {
        $headings = ['User', 'Role', 'Action', 'Module', 'Description', 'IP Address', 'Date & Time'];

        $rows = ActivityLog::query()
            ->when($request->module, fn($q) => $q->where('module', $request->module))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->get()
            ->map(fn($log) => [
                'User'        => $log->user_name,
                'Role'        => $log->user_role,
                'Action'      => ucfirst($log->action),
                'Module'      => $log->module,
                'Description' => $log->description,
                'IP Address'  => $log->ip_address ?? 'N/A',
                'Date & Time' => $log->created_at->format('M d, Y h:i A'),
            ])->toArray();

        if ($format === 'excel') {
            return $this->streamExcel('activity-log.xlsx', $headings, $rows);
        }

        return $this->streamPdf('Activity Log Report', $headings, array_map('array_values', $rows), 'activity-log.pdf');
    }

    private function exportUsers(string $format)
    {
        $headings = ['Name', 'Email', 'ID Number', 'Role', 'Joined'];

        $rows = User::all()->map(fn($user) => [
            'Name'      => $user->name,
            'Email'     => $user->email,
            'ID Number' => $user->id_number,
            'Role'      => ucfirst($user->role),
            'Joined'    => $user->created_at->format('M d, Y'),
        ])->toArray();

        if ($format === 'excel') {
            return $this->streamExcel('users.xlsx', $headings, $rows);
        }

        return $this->streamPdf('User Report', $headings, array_map('array_values', $rows), 'users.pdf');
    }

    private function streamExcel(string $filename, array $headings, array $rows)
    {
        return response()->streamDownload(function () use ($filename, $headings, $rows) {
            $writer = SimpleExcelWriter::streamDownload($filename);
            $writer->addHeader($headings);
            foreach ($rows as $row) {
                $writer->addRow($row);
            }
            $writer->toBrowserOutput();
        }, $filename);
    }

    private function streamPdf(string $title, array $headings, array $rows, string $filename)
    {
        $pdf = Pdf::loadView('reports.default', [
            'title'    => $title,
            'headings' => $headings,
            'rows'     => $rows,
        ])->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
