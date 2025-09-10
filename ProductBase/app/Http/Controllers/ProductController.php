<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:255|unique:products',
                'price' => 'required|numeric',
                'category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'url', // Validate each image URL if provided
            ]);

            $product = Product::create($validatedData);
            return response()->json($product, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'sku' => 'sometimes|required|string|max:255|unique:products,sku,' . $id,
                'price' => 'sometimes|required|numeric',
                'category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'url', // Validate each image URL if provided
            ]);

            $product->update($validatedData);
            return response()->json($product);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function upsert(Request $request)
    {
        $input = $request->has('products') ? $request->input('products') : $request->all();

        $validated = validator(
            ['products' => $input],
            [
                'products' => 'required|array|min:1',
                'products.*.sku' => 'required|string|max:255|distinct', // prevents duplicates inside the same request
                'products.*.name' => 'required|string|max:255',
                'products.*.price' => 'required|numeric',
                'products.*.category' => 'nullable|string|max:255',
                'products.*.description' => 'nullable|string',
                'products.*.images' => 'nullable|array',
                'products.*.images.*' => 'url',
            ]
        )->validate()['products'];

        $skus = collect($validated)->pluck('sku')->all();
        $existingSkus = \App\Models\Product::query()
            ->whereIn('sku', $skus)
            ->pluck('sku')
            ->all();

        $existingSet = array_flip($existingSkus);
        $updatedCount = count($existingSkus);
        $createdCount = count($skus) - $updatedCount;

        $now = now();

        $rows = collect($validated)->map(function ($p) use ($now) {
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
