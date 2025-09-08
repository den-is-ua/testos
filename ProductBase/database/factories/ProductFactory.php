<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'sku' => $this->faker->unique()->ean13,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'category' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'images' => [$this->faker->imageUrl(), $this->faker->imageUrl()],
        ];
    }
}
