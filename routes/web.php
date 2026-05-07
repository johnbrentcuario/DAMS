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
use App\Http\Controllers\GlobalSearchController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::post('/files/bulk-delete', [FileController::class, 'bulkDelete'])->name('files.bulk-delete');
Route::post('/files/bulk-move', [FileController::class, 'bulkMove'])->name('files.bulk-move');
Route::post('/files/bulk-change-type', [FileController::class, 'bulkChangeType'])->name('files.bulk-change-type');
Route::get('/files/bulk-export', [FileController::class, 'bulkExport'])->name('files.bulk-export');

Route::middleware(['auth'])->get('/search', GlobalSearchController::class)->name('search');
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
