<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportRequest;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Import;

class ImportController extends Controller
{
    public function store(StoreImportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        
        ImportService::store($file);

        return response()->json([
            'success' => true
        ], 201);
    }
}
