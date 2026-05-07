<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\PhysicalLocation;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Folders
        File::with('list', 'physical_location')
            ->where('fullname', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->each(function ($file) use (&$results) {
                $results[] = [
                    'type'     => 'folder',
                    'id'       => $file->id,
                    'title'    => $file->fullname,
                    'subtitle' => $file->list?->name ?? 'No employment type',
                    'meta'     => $file->physical_location?->name ?? 'No location',
                    'url'      => '/files',
                ];
            });

        // Search Users
        User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('id_number', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->each(function ($user) use (&$results) {
                $results[] = [
                    'type'     => 'user',
                    'id'       => $user->id,
                    'title'    => $user->name,
                    'subtitle' => $user->email,
                    'meta'     => ucfirst($user->role),
                    'url'      => '/users',
                ];
            });

        // Search Locations
        PhysicalLocation::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->each(function ($loc) use (&$results) {
                $results[] = [
                    'type'     => 'location',
                    'id'       => $loc->id,
                    'title'    => $loc->name,
                    'subtitle' => implode(', ', $loc->storage_paths ?? []),
                    'meta'     => '',
                    'url'      => '/physical-locations',
                ];
            });

        return response()->json($results);
    }
}
