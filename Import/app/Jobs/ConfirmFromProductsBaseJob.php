<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ConfirmFromProductsBaseJob implements ShouldQueue
{
    use Queueable;


    public function __construct(public int $importId)
    {
        //
    }

    public function handle(): void
    {
        $import = Import::findOrFail($this->importId);
        $import->increment('confirmed_iterations');

        if ($import->total_iterations == $import->confirmed_iterations) {
            $import->completed_at = now();
            $import->save();
        }

        //TODO implement pusher notification of progress!
    }

    public function viaConnection() { return 'rabbitmq'; }
    public function viaQueue()      { return 'import_confirmations'; }
}
