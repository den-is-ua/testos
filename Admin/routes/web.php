<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::post('imports', [ImportController::class, 'upload'])->name('import.upload');
Route::get('products', [ProductController::class, 'index'])->name('products.index');
