<?php

namespace Tests\Feature;

use App\Jobs\SetupImportSettingsByAIJob;
use App\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function store_endpoint_stores_file_and_creates_import()
    {
        Storage::fake('local');
        Queue::fake();

        $file = UploadedFile::fake()->create('test.csv', 10, 'text/csv');

        $response = $this->postJson('/api/imports', [
            'file' => $file,
        ]);

        Queue::assertPushed(SetupImportSettingsByAIJob::class);

        $response->assertStatus(201)->assertJson(['success' => true]);

        // Assert file exists in storage
        Storage::disk('local')->assertExists($file->hashName());

        // Assert database has import record
        $this->assertDatabaseHas('imports', [
            'file_name' => 'test.csv',
            'file_extension' => 'csv',
        ]);
    }

    /**
     * @test
     */
    public function store_endpoint_rejects_duplicate_in_progress_hash()
    {
        Storage::fake('local');
        Queue::fake();

        $file = UploadedFile::fake()->create('duplicate.csv', 10, 'text/csv');

        // compute the sha256 hash of the fake uploaded file
        $hash = hash_file('sha256', $file->getRealPath());

        // create an existing import record that is in progress (import_completed_at = null)
        Import::create([
            'file_name' => 'duplicate.csv',
            'file_path' => 'imports/' . $file->hashName(),
            'file_extension' => 'csv',
            'hash_content' => $hash,
            'settings' => [],
        ]);

        $response = $this->postJson('/api/imports', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);

        $errors = $response->json('errors.file') ?? [];
        $this->assertNotEmpty($errors, 'Expected validation error for file field.');

        $this->assertStringContainsString(
            'An import with the same file content is already in progress.',
            $errors[0]
        );
    }
}
