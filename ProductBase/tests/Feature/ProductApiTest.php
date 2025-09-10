<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    /** @test */
        public function it_can_create_a_product()
        {
            $productData = [
                'name' => $this->faker->word,
                'sku' => $this->faker->unique()->ean13,
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'category' => $this->faker->word,
                'description' => $this->faker->paragraph,
                'images' => [$this->faker->imageUrl(), $this->faker->imageUrl()],
            ];

            $response = $this->postJson('/api/products', $productData);

            $response->assertStatus(201)
                    ->assertJsonFragment(['name' => $productData['name']]);

            $this->assertDatabaseHas('products', ['sku' => $productData['sku']]);
        }

    /** @test */
    public function it_can_retrieve_a_list_of_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_retrieve_a_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $product->name]);
    }

    /** @test */
    public function it_returns_404_if_product_not_found()
    {
        $response = $this->getJson('/api/products/999'); // Assuming 999 does not exist

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Product not found']);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $product = Product::factory()->create();
        $updatedData = [
            'name' => 'Updated Product Name',
            'price' => 199.99,
        ];

        $response = $this->putJson('/api/products/' . $product->id, $updatedData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Product Name', 'price' => 199.99]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product Name', 'price' => 199.99]);
    }

    /** @test */
    public function it_returns_404_when_updating_non_existent_product()
    {
        $updatedData = [
            'name' => 'Updated Product Name',
        ];

        $response = $this->putJson('/api/products/999', $updatedData);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Product not found']);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_product()
    {
        $response = $this->deleteJson('/api/products/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Product not found']);
    }

    /** @test */
    public function it_validates_product_creation_data()
    {
        $response = $this->postJson('/api/products');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'price', 'sku']);
    }

    /** @test */
    public function it_validates_product_update_data()
    {
        $product = Product::factory()->create();

        $response = $this->putJson('/api/products/' . $product->id, [
            'name' => '', // Invalid name
            'price' => 'not-a-number', // Invalid price
            'sku' => $product->sku, // SKU already exists for this product, but should be unique if changed
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'price']);
    }

    /** @test */
    public function it_can_upsert_new_products()
    {
        $productsToUpsert = [
            [
                'sku' => $this->faker->unique()->ean13,
                'name' => $this->faker->word,
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'category' => $this->faker->word,
                'description' => $this->faker->paragraph,
                'images' => [$this->faker->imageUrl(), $this->faker->imageUrl()],
            ],
            [
                'sku' => $this->faker->unique()->ean13,
                'name' => $this->faker->word,
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'category' => $this->faker->word,
                'description' => $this->faker->paragraph,
                'images' => [$this->faker->imageUrl()],
            ],
        ];

        $response = $this->postJson('/api/products/upsert', ['products' => $productsToUpsert]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Upsert completed: 2 created, 0 updated.',
            'meta' => [
                'total' => 2,
                'created' => 2,
                'updated' => 0,
            ],
            'data' => [
                'affected_skus' => array_column($productsToUpsert, 'sku'),
            ],
        ]);

        // Assert that products are in the database
        foreach ($productsToUpsert as $productData) {
            $this->assertDatabaseHas('products', ['sku' => $productData['sku'], 'name' => $productData['name']]);
        }
    }

    /** @test */
    public function it_can_upsert_existing_products()
    {
        // Create some initial products
        $existingProducts = Product::factory()->count(2)->create([
            'name' => 'Original Name',
            'price' => 100.00,
        ]);

        $productsToUpsert = [
            [
                'sku' => $existingProducts[0]->sku, // Existing SKU
                'name' => 'Updated Name 1',
                'price' => 150.00,
                'category' => 'New Category 1',
                'description' => 'Updated description 1',
                'images' => [$this->faker->imageUrl()],
            ],
            [
                'sku' => $existingProducts[1]->sku, // Existing SKU
                'name' => 'Updated Name 2',
                'price' => 200.00,
                'category' => 'New Category 2',
                'description' => 'Updated description 2',
                'images' => [$this->faker->imageUrl(), $this->faker->imageUrl()],
            ],
        ];

        $response = $this->postJson('/api/products/upsert', ['products' => $productsToUpsert]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Upsert completed: 0 created, 2 updated.',
            'meta' => [
                'total' => 2,
                'created' => 0,
                'updated' => 2,
            ],
            'data' => [
                'affected_skus' => array_column($productsToUpsert, 'sku'),
            ],
        ]);

        // Assert that products are updated in the database
        foreach ($productsToUpsert as $productData) {
            $this->assertDatabaseHas('products', ['sku' => $productData['sku'], 'name' => $productData['name'], 'price' => $productData['price']]);
        }
    }

    /** @test */
    public function it_can_upsert_mixed_new_and_existing_products()
    {
        // Create some initial products
        $existingProduct = Product::factory()->create([
            'name' => 'Existing Product Name',
            'price' => 50.00,
        ]);

        $productsToUpsert = [
            // Existing product
            [
                'sku' => $existingProduct->sku,
                'name' => 'Updated Existing Product Name',
                'price' => 75.00,
            ],
            // New product
            [
                'sku' => $this->faker->unique()->ean13,
                'name' => $this->faker->word,
                'price' => $this->faker->randomFloat(2, 10, 1000),
            ],
        ];

        $response = $this->postJson('/api/products/upsert', ['products' => $productsToUpsert]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Upsert completed: 1 created, 1 updated.',
            'meta' => [
                'total' => 2,
                'created' => 1,
                'updated' => 1,
            ],
            'data' => [
                'affected_skus' => array_column($productsToUpsert, 'sku'),
            ],
        ]);

        // Assert that the existing product is updated
        $this->assertDatabaseHas('products', [
            'sku' => $existingProduct->sku,
            'name' => 'Updated Existing Product Name',
            'price' => 75.00,
        ]);

        // Assert that the new product is created
        $this->assertDatabaseHas('products', [
            'sku' => $productsToUpsert[1]['sku'],
            'name' => $productsToUpsert[1]['name'],
            'price' => $productsToUpsert[1]['price'],
        ]);
    }

    /** @test */
    public function it_fails_to_upsert_with_missing_required_fields()
    {
        $productsToUpsert = [
            // Missing name and price
            [
                'sku' => $this->faker->unique()->ean13,
            ],
            // Missing sku
            [
                'name' => $this->faker->word,
                'price' => $this->faker->randomFloat(2, 10, 1000),
            ],
        ];

        $response = $this->postJson('/api/products/upsert', ['products' => $productsToUpsert]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'products.0.name',
            'products.0.price',
            'products.1.sku',
        ]);
    }

    /** @test */
    public function it_fails_to_upsert_with_duplicate_skus_in_request()
    {
        $sku = $this->faker->unique()->ean13;

        $productsToUpsert = [
            [
                'sku' => $sku,
                'name' => $this->faker->word,
                'price' => $this->faker->randomFloat(2, 10, 1000),
            ],
            [
                'sku' => $sku, // Duplicate SKU
                'name' => $this->faker->word,
                'price' => $this->faker->randomFloat(2, 10, 1000),
            ],
        ];

        $response = $this->postJson('/api/products/upsert', ['products' => $productsToUpsert]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products.1.sku']);
    }

    /** @test */
    public function it_fails_to_upsert_with_missing_products_key()
    {
        $response = $this->postJson('/api/products/upsert', []); // Missing 'products' key

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'products',
        ]);
    }
}
