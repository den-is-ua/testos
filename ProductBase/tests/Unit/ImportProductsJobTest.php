<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use App\Jobs\ImportProductsJob;
use App\Jobs\ConfirmImportJob;

class ImportProductsJobTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_calls_upsert_and_dispatches_confirmation(): void
    {
        Queue::fake();

        $importId = 123;
        $products = [
            ['sku' => 'SKU1', 'name' => 'Product A', 'price' => 10],
            ['sku' => 'SKU2', 'name' => 'Product B', 'price' => 20],
        ];

        // Mock the static UpsertProductService::upsert call using Mockery alias.
        // Note: avoid referencing App\Services\UpsertProductService directly to allow alias mock.
        $upsertMock = Mockery::mock('alias:App\Services\UpsertProductService');
        $upsertMock->shouldReceive('upsert')
            ->once()
            ->with($products)
            ->andReturn([
                'updatedCount' => 0,
                'createdCount' => 2,
                'total' => 2,
            ]);

        $job = new ImportProductsJob($importId, $products);
        $job->handle();

        Queue::assertPushed(ConfirmImportJob::class, function ($dispatchedJob) use ($importId) {
            return $dispatchedJob->importId === $importId;
        });
    }
}
