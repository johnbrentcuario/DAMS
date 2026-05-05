<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->search, fn ($q) =>
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('user_name', 'like', "%{$request->search}%")
                  ->orWhere('module', 'like', "%{$request->search}%")
            )
            ->when($request->module, fn ($q) =>
                $q->where('module', $request->module)
            )
            ->when($request->action, fn ($q) =>
                $q->where('action', $request->action)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('ActivityLog/Index', [
            'logs'    => $logs,
            'filters' => $request->only(['search', 'module', 'action']),
        ]);
    }
}
