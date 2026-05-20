<?php

namespace App\Http\Controllers;

use App\Models\SeparationMode;
use App\Services\ActivityLogger;
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

        $separationMode = SeparationMode::create($validated);

        ActivityLogger::log(
            'created',
            'separation-modes',
            "Created separation mode \"{$separationMode->name}\"" .
            ($separationMode->color ? " | Color: {$separationMode->color}" : '') .
            ($separationMode->description ? " | Description: {$separationMode->description}" : '')
        );

        return back()->with('success', 'Mode of separation created successfully.');
    }

    public function update(Request $request, SeparationMode $separationMode)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:separation_modes,name,' . $separationMode->id . ',id,deleted_at,NULL',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
        ]);

        $before = [
            'name'        => $separationMode->name,
            'description' => $separationMode->description ?? 'empty',
            'color'       => $separationMode->color ?? 'None',
        ];

        $separationMode->update($validated);

        $after = [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? 'empty',
            'color'       => $validated['color'] ?? 'None',
        ];

        ActivityLogger::logChanges(
            'updated',
            'separation-modes',
            "Updated separation mode \"{$separationMode->name}\"",
            $before,
            $after
        );

        return back()->with('success', 'Mode of separation updated successfully.');
    }

    public function destroy(SeparationMode $separationMode)
    {
        ActivityLogger::log(
            'deleted',
            'separation-modes',
            "Deleted separation mode \"{$separationMode->name}\"" .
            ($separationMode->color ? " | Color: {$separationMode->color}" : '') .
            ($separationMode->description ? " | Description: {$separationMode->description}" : '') .
            " | Was used by {$separationMode->files()->count()} record(s)"
        );

        $separationMode->delete();

        return back()->with('success', 'Mode of separation deleted successfully.');
    }
}
