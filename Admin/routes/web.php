<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::post('/imports', [ImportController::class, 'upload'])->name('import.upload');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
