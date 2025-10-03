<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SetupImportSettingsByAIJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $importId)
    {
        //
    }

    public function handle(): void
    {
        $importService = new ImportService(Import::findOrFail($this->importId));
        $importService->setupSettingsByGemini();
        ParseImportJob::dispatch($this->importId);
    }
}
