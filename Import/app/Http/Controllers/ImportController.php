<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use App\Jobs\SetupImportSettingsByAIJob;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function store(StoreImportRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $import = ImportService::store($file);
        SetupImportSettingsByAIJob::dispatch($import->id);

        return response()->json([
            'success' => true,
            'data' => new ImportResource($import),
        ], 201);
    }
}
