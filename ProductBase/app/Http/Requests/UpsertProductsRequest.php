<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the product IDs from the request parameters for unique rule
        // This assumes the 'products' input is an array of products, and each product has a 'sku'.
        // For upsert, we need to ignore the current SKU when checking for uniqueness if the product already exists.
        // However, the original controller uses a custom validator for this, which is a bit more complex.
        // For simplicity and to move validation to a Form Request, we'll adapt it.
        // The original uses `distinct` for products.*.sku, and then a separate query to find existing SKUs.
        // A Form Request can handle this by using Rule::unique with an ignore callback if we can get the ID.
        // Since the upsert method doesn't directly pass IDs for each product in the request,
        // we'll rely on the `distinct` rule for SKUs within the batch and let the upsert logic handle
        // the actual unique constraint on the database level.
        // If a more robust unique check per item in the batch is needed, it would require more complex logic,
        // potentially involving fetching existing products by SKU first, which is what the original controller does.

        // For now, we'll replicate the structure of the original validator as closely as possible.
        // The original validator:
        // 'products.*.sku' => 'required|string|max:255|distinct',
        // The `distinct` rule ensures that within the submitted `products` array, no two SKUs are the same.
        // The actual database unique constraint is handled by the `upsert` method's `unique:products` rule.

        return [
            'products' => 'required|array|min:1',
            'products.*.sku' => [
                'required',
                'string',
                'max:255',
                'distinct', // Ensures SKUs are unique within the submitted batch
            ],
            'products.*.name' => 'required|string|max:255',
            'products.*.price' => 'required|numeric',
            'products.*.category' => 'nullable|string|max:255',
            'products.*.description' => 'nullable|string',
            'products.*.images' => 'nullable|array',
            'products.*.images.*' => 'url',
        ];
    }
}
