<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('returns products data on success', function () {
    Config::set('clients.product_base_host', 'https://product-base.test');
    Config::set('clients.product_base_key', 'testkey');

    $responseBody = [
        'data' => [
            ['id' => 1, 'name' => 'Product A'],
        ],
        'meta' => ['total' => 1, 'per_page' => 1, 'page' => 1],
    ];

    Http::fake([
        'https://product-base.test/*' => Http::response($responseBody, 200),
    ]);

    $response = $this->getJson('/products?page=1&per_page=1&filter=');

    $response->assertStatus(200)
        ->assertJson([
            'data' => $responseBody['data'],
            'meta' => $responseBody['meta'],
        ]);
});

it('passes through 422 from product-base service', function () {
    Config::set('clients.product_base_host', 'https://product-base.test');
    Config::set('clients.product_base_key', 'testkey');

    $body = [
        'message' => 'Invalid request to product service',
        'errors' => ['page' => ['must be numeric']],
    ];

    Http::fake([
        'https://product-base.test/*' => Http::response($body, 422),
    ]);

    $response = $this->getJson('/products?page=1');

    $response->assertStatus(422)
        ->assertJson([
            'message' => $body['message'],
            'errors' => $body['errors'],
        ]);
});

it('validates product request parameters and returns 422', function () {
    // Validation happens before external client is called, so no need to set clients config here.
    $response = $this->getJson('/products?filter=' . str_repeat('a', 11));

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['filter'],
        ]);
});
