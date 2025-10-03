<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ImportUploadRequest;
use App\Services\ImportClient;

class ImportController extends Controller
{
    public function upload(ImportUploadRequest $request)
    {
        $response = ImportClient::autoapplyConfigs()->importFile($request->file('file'));

        if ($response->getStatusCode() == 422) {
            return response()->json([
                'message' => $response->json('message'),
                'errors' => $response->json('errors'),
            ], 422);
        }

        return response()->json([
            'message' => 'Import was uploaded',
            'data' => $response->json('data'),
        ]);
    }
}
