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

    /**
     * Upsert products (update if exists, create if not). Handles an array of products.
     */
    public function upsert(Request $request)
    {
        try {
            // Validate the incoming array of products
            $validatedData = $request->validate([
                '*.sku' => 'required|string|max:255|unique:products,sku', // SKU is required for each product
                '*.name' => 'required|string|max:255',
                '*.price' => 'required|numeric',
                '*.category' => 'nullable|string|max:255',
                '*.description' => 'nullable|string',
                '*.images' => 'nullable|array',
                '*.images.*' => 'url', // Validate each image URL if provided
            ]);

            $results = [];
            $createdCount = 0;
            $updatedCount = 0;

            foreach ($validatedData as $productData) {
                // Find product by SKU
                $product = Product::where('sku', $productData['sku'])->first();

                if ($product) {
                    // Product exists, update it
                    $product->update($productData);
                    $results[] = $product;
                    $updatedCount++;
                } else {
                    // Product does not exist, create it
                    $product = Product::create($productData);
                    $results[] = $product;
                    $createdCount++;
                }
            }

            return response()->json([
                'message' => "Upsert operation completed. {$createdCount} created, {$updatedCount} updated.",
                'data' => $results
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
