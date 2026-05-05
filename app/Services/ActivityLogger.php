<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, string $module, string $description): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name ?? 'System',
            'user_role'   => $user?->role ?? 'system',
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => Request::ip(),
        ]);
    }

    public static function logChanges(string $action, string $module, string $subject, array $before, array $after): void
    {
        $changes = [];

        foreach ($after as $key => $newValue) {
            $oldValue = $before[$key] ?? null;

            // Skip password and timestamps
            if (in_array($key, ['password', 'updated_at', 'created_at', 'remember_token'])) {
                continue;
            }

            $old = is_array($oldValue) ? implode(', ', $oldValue) : ($oldValue ?? 'empty');
            $new = is_array($newValue) ? implode(', ', $newValue) : ($newValue ?? 'empty');

            if ($old !== $new) {
                $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": \"{$old}\" → \"{$new}\"";
            }
        }

        $description = $changes
            ? "{$subject} | Changes: " . implode(' | ', $changes)
            : "{$subject} | No changes detected";

        self::log($action, $module, $description);
    }
}
