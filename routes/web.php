<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PhysicalLocationController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// CHANGE: Point this to the DashboardController class instead of an inline function
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Resources
Route::resource('lists', ListController::class);
Route::resource('files', FileController::class);
Route::resource('physical-locations', PhysicalLocationController::class);

// Custom File Actions
Route::post('/files/{file}/upload', [FileController::class, 'upload'])->name('files.upload');
Route::delete('/files/{file}/upload', [FileController::class, 'removeAttachment'])->name('files.remove_upload');

require __DIR__.'/settings.php';
