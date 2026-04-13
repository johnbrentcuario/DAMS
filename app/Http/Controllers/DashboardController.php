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
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalFiles'     => File::count(),
                'totalLists'     => FileList::count(),
                'totalLocations' => PhysicalLocation::count(),
                // Eager load counts for the progress bars
                'listBreakdown'  => FileList::withCount('files')->get(),
                // Eager load relationships for the recent side panel
                'recentFiles'    => File::with(['list', 'physical_location'])
                                    ->latest()
                                    ->take(10)
                                    ->get(),
            ],
            'locations' => PhysicalLocation::latest()->get(),
        ]);
    }
}
