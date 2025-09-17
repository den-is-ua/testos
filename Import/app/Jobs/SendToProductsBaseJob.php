<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendToProductsBaseJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public $importId, public $products) {}

    public function handle() {}

    public function viaConnection() { return 'rabbitmq'; }
    public function viaQueue()      { return 'imports'; }
}
