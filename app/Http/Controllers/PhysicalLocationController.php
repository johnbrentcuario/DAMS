<?php
namespace App\Http\Controllers;

use App\Models\PhysicalLocation;
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
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'storage_paths' => 'nullable|array',
        ]);

        PhysicalLocation::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, PhysicalLocation $physical_location)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'nullable|string|max:7',
        'storage_paths' => 'nullable|array',
    ]);

    $physical_location->update($validated);
    return redirect()->back();
}

public function destroy(PhysicalLocation $physical_location)
{
    $physical_location->delete();
    return redirect()->back();
}
}
