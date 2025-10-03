<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpsertProductService
{
    public static function upsert(array $products)
    {
        $skus = collect($products)->pluck('sku')->all();
        $existingSkus = Product::query()
            ->whereIn('sku', $skus)
            ->pluck('sku')
            ->all();

        $existingSet = array_flip($existingSkus);
        $updatedCount = count($existingSkus);
        $createdCount = count($skus) - $updatedCount;

        $now = now();

        $rows = collect($products)->map(function ($p) use ($now) {
            return [
                'sku' => $p['sku'],
                'name' => $p['name'],
                'price' => $p['price'],
                'category' => $p['category'] ?? null,
                'description' => $p['description'] ?? null,
                'images' => array_key_exists('images', $p) ? json_encode($p['images'] ?? []) : null,
                'updated_at' => $now,
            ];
        });

        DB::transaction(function () use ($rows) {
            foreach ($rows->chunk(1000) as $chunk) {
                Product::query()->upsert(
                    $chunk->all(),
                    ['sku'],
                    ['name', 'price', 'category', 'description', 'images', 'updated_at']
                );
            }
        });

        return [
            'updatedCount' => $updatedCount,
            'createdCount' => $createdCount,
            'total' => count($skus),
        ];
    }
}
