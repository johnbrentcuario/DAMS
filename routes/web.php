<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ListController;
use App\Http\Controllers\FileController;
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('lists', ListController::class);
Route::resource('files', FileController::class);
Route::post('/files/{file}/upload', [FileController::class, 'upload'])->name('files.upload');
Route::delete('/files/{file}/upload', [FileController::class, 'removeAttachment'])->name('files.remove_upload');

require __DIR__.'/settings.php';
