<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'id_number', 'role', 'created_at')
            ->latest()
            ->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'id_number' => ['required', 'string', 'max:255', 'unique:users'],
            'role'      => ['required', 'in:admin,staff'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'id_number' => $validated['id_number'],
            'role'      => $validated['role'],
            'password'  => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            'created',
            'users',
            "Created user \"{$user->name}\" | Email: {$user->email} | ID: {$user->id_number} | Role: {$user->role}"
        );

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name'      => ['required', 'string', 'max:255'],
        'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        'id_number' => ['required', 'string', 'max:255', 'unique:users,id_number,' . $user->id],
        'role'      => ['required', 'in:admin,staff'],
        'password'  => ['nullable', 'confirmed', Rules\Password::defaults()],
    ]);

    $before = [
        'name'      => $user->name,
        'email'     => $user->email,
        'id_number' => $user->id_number,
        'role'      => $user->role,
    ];

    $after = [
        'name'      => $validated['name'],
        'email'     => $validated['email'],
        'id_number' => $validated['id_number'],
        'role'      => $validated['role'],
    ];

    $user->update([
        'name'      => $validated['name'],
        'email'     => $validated['email'],
        'id_number' => $validated['id_number'],
        'role'      => $validated['role'],
        ...(!empty($validated['password']) ? ['password' => Hash::make($validated['password'])] : []),
    ]);

    // Build log with optional password note
    $passwordNote = !empty($validated['password'])
        ? ' | Password: (changed)'
        : '';

    ActivityLogger::logChanges(
        'updated',
        'users',
        "Updated user \"{$user->name}\"{$passwordNote}",
        $before,
        $after
    );

    return redirect()->route('users.index')->with('success', 'User updated successfully.');
}

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        ActivityLogger::log(
            'deleted',
            'users',
            "Deleted user \"{$user->name}\" | Email: {$user->email} | ID: {$user->id_number} | Role: {$user->role}"
        );

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
