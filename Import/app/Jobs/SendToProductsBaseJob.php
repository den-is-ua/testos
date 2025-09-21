<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SendToProductsBaseJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public $importId, public $products) {
        $this->onQueue('imports');
    }

     public function handle(): void
     {}
}
