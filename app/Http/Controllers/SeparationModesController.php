<?php

namespace App\Http\Controllers;

use App\Models\SeparationMode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeparationModesController extends Controller
{
    public function index()
    {
        $separationModes = SeparationMode::withCount('files')
            ->latest()
            ->paginate(15);

        return Inertia::render('SeparationModes/Index', [
            'separationModes' => $separationModes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:separation_modes,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
        ]);

        SeparationMode::create($validated);

        return back()->with('success', 'Mode of separation created successfully.');
    }

    public function update(Request $request, SeparationMode $separationMode)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:separation_modes,name,' . $separationMode->id . ',id,deleted_at,NULL',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
        ]);

        $separationMode->update($validated);

        return back()->with('success', 'Mode of separation updated successfully.');
    }

    public function destroy(SeparationMode $separationMode)
    {
        $separationMode->delete();

        return back()->with('success', 'Mode of separation deleted successfully.');
    }
}
