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

        return response()->json([
            'message' => 'Import was uploaded',
            'data' => $response->json('data')
        ]);
    }
}
