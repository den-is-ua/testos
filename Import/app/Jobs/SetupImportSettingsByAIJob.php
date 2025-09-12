<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SetupImportSettingsByAIJob implements ShouldQueue
{
    use Queueable;


    public function __construct(Import $import)
    {
        //
    }

    public function handle(): void
    {
        
    }
}
