<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\FileList;
use Inertia\Inertia;
use Inertia\Response;
class ListController extends Controller
{
    public function index(): Response
    {
        $lists = FileList::query()
            ->select(['id', 'name', 'color', 'created_at'])
            ->withCount('files')
            ->latest()
            ->get();

        return Inertia::render('lists/index', [
            'lists' => $lists,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'], 
        ]);

        $list = FileList::create($validated);

        return redirect()->route('lists.index');
    }

    public function update(Request $request, FileList $list): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'], 
        ]);

        $list->update($validated);

        return redirect()->route('lists.index');
    }

    public function destroy(FileList $list): RedirectResponse
    {
        $list->delete();

        return redirect()->route('lists.index');
    }
}
