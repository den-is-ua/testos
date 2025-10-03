<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\AMQSender;
use App\Services\CSVParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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

        Log::debug(__CLASS__ . __METHOD__ . '. Start parsing file id: ' . $this->importId);
        foreach ($service->parse() as $products) {
            if (config('app.env') != 'testing') {
                $AMQSender->sendProducts($import->id, $products);
            }

            $import->increment('total_iterations');
        }
        Log::debug(__CLASS__ . __METHOD__ . '. Done parsing file id: ' . $this->importId);
    }
}
