<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => 'required|array|min:1',
            'products.*.sku' => [
                'required',
                'string',
                'max:255',
                'distinct',
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
