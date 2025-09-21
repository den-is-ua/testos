<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductRequest;
use App\Services\ProductBaseClient;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(IndexProductRequest $reqest)
    {
        $response = ProductBaseClient::autoapplyConfigs()->getProducts(
            $reqest->get('page', 1),
            $reqest->get('per_page', 1),
            $reqest->get('filter', '')
        );

        if ($response->getStatusCode() == 422) {
            return response()->json([
                'message' => $response->json('message'),
                'errors' => $response->json('errors')
            ], 422);
        }

        return response()->json([
            'data' => $response->json('data'),
            'meta' => $response->json('meta')
        ]);
    }
}
