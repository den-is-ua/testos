<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UpsertProductsRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB; // Added for DB::transaction

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();

        $product = Product::create($validatedData);
        return response()->json($product, 201);
    }

    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validatedData = $request->validated();

        $product->update($validatedData);
        return response()->json($product);
    }

    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
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
            'message' => "Upsert completed: {$createdCount} created, {$updatedCount} updated.",
            'meta' => [
                'total'   => count($skus),
                'created' => $createdCount,
                'updated' => $updatedCount,
            ],
            'data' => [
                'affected_skus' => $skus,
            ],
        ], 200);
    }
}
