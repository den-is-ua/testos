<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('hc', fn () => 'Import Service Deployed!'); // HealthCheck
Route::post('imports', [ImportController::class, 'store']);
