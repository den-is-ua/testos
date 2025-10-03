<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UpsertProductsRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\UpsertProductService;

class ProductController extends Controller
{
    public function index(IndexProductRequest $request)
    {
        $products = Product::paginate(
            perPage: $request->integer('per_page', 15)
        );

        return new ProductCollection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();

        $product = Product::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();
        $product->update($validatedData);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['success' => true]);
    }

    public function upsert(UpsertProductsRequest $request)
    {
        $validatedData = $request->validated()['products']; // Use validated data directly

        $upsertInfo = UpsertProductService::upsert($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Upsert completed',
            'data' => $upsertInfo,
        ], 200);
    }
}
