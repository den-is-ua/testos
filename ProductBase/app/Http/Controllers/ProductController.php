<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UpsertProductsRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
        return response()->json(['success' => true, 'message' => '', 'data' => $product], 201);
    }

    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found', 'data' => null], 404);
        }

        return response()->json(['success' => true, 'message' => '', 'data' => $product]);
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found', 'data' => null], 404);
        }

        $validatedData = $request->validated();

        $product->update($validatedData);
        return response()->json(['success' => true, 'message' => '', 'data' => $product]);
    }

    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found', 'data' => null], 404);
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully', 'data' => null]);
    }

    public function upsert(UpsertProductsRequest $request)
    {
        $validatedData = $request->validated()['products']; // Use validated data directly

        $skus = collect($validatedData)->pluck('sku')->all();
        $existingSkus = \App\Models\Product::query()
            ->whereIn('sku', $skus)
            ->pluck('sku')
            ->all();

        $existingSet = array_flip($existingSkus);
        $updatedCount = count($existingSkus);
        $createdCount = count($skus) - $updatedCount;

        $now = now();

        $rows = collect($validatedData)->map(function ($p) use ($now) {
            return [
                'sku'         => $p['sku'],
                'name'        => $p['name'],
                'price'       => $p['price'],
                'category'    => $p['category'] ?? null,
                'description' => $p['description'] ?? null,
                'images'      => array_key_exists('images', $p) ? json_encode($p['images'] ?? []) : null,
                'updated_at'  => $now,
            ];
        });

        DB::transaction(function () use ($rows) {
            foreach ($rows->chunk(1000) as $chunk) {
                \App\Models\Product::query()->upsert(
                    $chunk->all(),
                    ['sku'],
                    ['name', 'price', 'category', 'description', 'images', 'updated_at']
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Upsert completed: {$createdCount} created, {$updatedCount} updated.",
            'data' => [
                'meta' => [
                    'total'   => count($skus),
                    'created' => $createdCount,
                    'updated' => $updatedCount,
                ],
                'affected_skus' => $skus,
            ],
        ], 200);
    }
}
