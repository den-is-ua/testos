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
}
