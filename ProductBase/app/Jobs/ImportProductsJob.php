<?php

namespace App\Jobs;

use App\Services\UpsertProductService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportProductsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $importId, public $products)
    {
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UpsertProductService::upsert($this->products);
        ConfirmImportJob::dispatch($this->importId);
    }
}
