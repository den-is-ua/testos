<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::post('/imports', [ImportController::class, 'upload'])->name('import.upload');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
