<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('uploads import file successfully', function () {
    Config::set('clients.import_host', 'https://import.test');
    Config::set('clients.import_key', 'testkey');

    $serviceBody = [
        'data' => ['import_id' => 123],
    ];

    Http::fake([
        'https://import.test/*' => Http::response($serviceBody, 200),
    ]);

    $file = UploadedFile::fake()->create('import.csv', 100);

    $response = $this->postJson('/imports', [
        'file' => $file,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Import was uploaded',
            'data' => $serviceBody['data'],
        ]);
});

it('passes through 422 from import service', function () {
    Config::set('clients.import_host', 'https://import.test');
    Config::set('clients.import_key', 'testkey');

    $body = [
        'message' => 'File invalid',
        'errors' => ['file' => ['Too large']],
    ];

    Http::fake([
        'https://import.test/*' => Http::response($body, 422),
    ]);

    $file = UploadedFile::fake()->create('import.csv', 100);

    $response = $this->postJson('/imports', [
        'file' => $file,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => $body['message'],
            'errors' => $body['errors'],
        ]);
});

it('validates import upload request and returns 422 for missing file', function () {
    $response = $this->postJson('/imports', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['file'],
        ]);
});
