<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\AMQSender;
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
    public function handle(AMQSender $AMQSender): void
    {
        $import = Import::find($this->importId);
        $service = new CSVParserService($import);
        
        foreach ($service->parse() as $products) {
            $AMQSender->sendProducts($import->id, $products);
            $import->increment('total_iterations');
        }
    }
}
