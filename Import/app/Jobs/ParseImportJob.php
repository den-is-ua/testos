<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\CSVParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ParseImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $importId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $import = Import::find($this->importId);
        $service = new CSVParserService($import);
        
        foreach ($service->parse() as $products) {
            SendToProductsBaseJob::dispatch($import->id, $products);
            $import->increment('total_iterations');
        }
    }
}
