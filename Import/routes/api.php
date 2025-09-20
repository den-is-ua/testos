<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;

Route::get('hc', fn() => 'Import Service Deployed!'); //HealthCheck
Route::post('imports', [ImportController::class, 'store']);