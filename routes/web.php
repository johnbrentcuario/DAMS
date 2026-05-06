<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PhysicalLocationController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReportsController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Resources
Route::resource('lists', ListController::class);
Route::resource('files', FileController::class);
Route::resource('physical-locations', PhysicalLocationController::class);
Route::resource('users', UsersController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware(['auth', 'admin']);
Route::get('activity-log', [ActivityLogController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('activity-log.index');
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportsController::class, 'export'])->name('reports.export');
});

// Custom File Actions
Route::post('/files/{file}/upload', [FileController::class, 'upload'])->name('files.upload');
Route::delete('/files/{file}/upload', [FileController::class, 'removeAttachment'])->name('files.remove_upload');


require __DIR__.'/settings.php';
