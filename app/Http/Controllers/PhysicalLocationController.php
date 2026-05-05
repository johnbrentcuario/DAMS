<?php

namespace App\Http\Controllers;

use App\Models\PhysicalLocation;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhysicalLocationController extends Controller
{
    public function index()
    {
        return Inertia::render('locations/Index', [
            'locations' => PhysicalLocation::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'color'         => ['nullable', 'string', 'max:7'],
            'storage_paths' => ['nullable', 'array'],
        ]);

        $location = PhysicalLocation::create($validated);

        $paths = !empty($location->storage_paths) ? implode(', ', $location->storage_paths) : 'None';

        ActivityLogger::log(
            'created',
            'locations',
            "Created location \"{$location->name}\" | Color: " . ($location->color ?? 'None') .
            " | Storage Paths: {$paths}"
        );

        return redirect()->back();
    }

    public function update(Request $request, PhysicalLocation $physical_location)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'color'         => ['nullable', 'string', 'max:7'],
            'storage_paths' => ['nullable', 'array'],
        ]);

        $before = [
            'name'          => $physical_location->name,
            'color'         => $physical_location->color ?? 'None',
            'storage_paths' => !empty($physical_location->storage_paths) ? implode(', ', $physical_location->storage_paths) : 'None',
        ];

        $physical_location->update($validated);

        $after = [
            'name'          => $validated['name'],
            'color'         => $validated['color'] ?? 'None',
            'storage_paths' => !empty($validated['storage_paths']) ? implode(', ', $validated['storage_paths']) : 'None',
        ];

        ActivityLogger::logChanges('updated', 'locations', "Updated location \"{$physical_location->name}\"", $before, $after);

        return redirect()->back();
    }

    public function destroy(PhysicalLocation $physical_location)
    {
        $paths = !empty($physical_location->storage_paths) ? implode(', ', $physical_location->storage_paths) : 'None';

        ActivityLogger::log(
            'deleted',
            'locations',
            "Deleted location \"{$physical_location->name}\" | Color: " . ($physical_location->color ?? 'None') .
            " | Storage Paths: {$paths}"
        );

        $physical_location->delete();

        return redirect()->back();
    }
}
