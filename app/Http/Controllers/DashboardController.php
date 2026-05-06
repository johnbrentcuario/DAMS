<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileList;
use App\Models\PhysicalLocation;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Calculate missing and complete documents
        $allFiles       = File::with('list')->get();
        $missingCount   = 0;
        $completeCount  = 0;

        foreach ($allFiles as $file) {
            $requirements = $file->list?->requirements ?? [];
            $attachments  = $file->attachments ?? [];

            if (empty($requirements)) continue;

            $missing = array_filter($requirements, fn($req) => !isset($attachments[$req]));

            if (empty($missing)) {
                $completeCount++;
            } else {
                $missingCount++;
            }
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalFiles'        => File::count(),
                'totalLists'        => FileList::count(),
                'totalLocations'    => PhysicalLocation::count(),
                'missingDocuments'  => $missingCount,
                'completeDocuments' => $completeCount,
                'listBreakdown'     => FileList::withCount('files')->get(),
                'locationBreakdown' => PhysicalLocation::withCount('files')->get(),
                'recentFiles'       => File::with(['list', 'physical_location'])
                                        ->latest()
                                        ->take(10)
                                        ->get(),
            ],
            'locations' => PhysicalLocation::latest()->get(),
        ]);
    }
}
