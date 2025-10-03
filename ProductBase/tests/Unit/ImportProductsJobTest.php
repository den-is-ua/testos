<?php

namespace Tests\Unit;

use App\Jobs\ConfirmImportJob;
use App\Jobs\ImportProductsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ImportProductsJobTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function calls_upsert_and_dispatches_confirmation(): void
    {
        Queue::fake();

        $importId = 123;
        $products = [
            ['sku' => 'SKU1', 'name' => 'Product A', 'price' => 10],
            ['sku' => 'SKU2', 'name' => 'Product B', 'price' => 20],
        ];

        $job = new ImportProductsJob($importId, $products);
        $job->handle();

        Queue::assertPushed(ConfirmImportJob::class, function ($dispatchedJob) use ($importId) {
            return $dispatchedJob->importId === $importId;
        });
    }
}
