<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Import;

class ImportController extends Controller
{
    public function store(StoreImportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('imports');
        $fullPath = storage_path('app/' . $path);
        $hash = file_exists($fullPath) ? hash_file('sha256', $fullPath) : null;

        Import::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => substr($file->getClientOriginalExtension() ?? '', 0, 4),
            'hash_content' => $hash
        ]);

        return response()->json([
            'success' => true
        ], 201);
    }
}
