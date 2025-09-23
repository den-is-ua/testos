<?php

namespace App\Console\Commands;

use App\OV\ProductOV;
use App\Services\AMQSender;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RMQTestCommand extends Command
{
    protected $signature = 'rmqt';

    public function handle(AMQSender $sender)
    {
        $sender->sendProducts(1, [
            new ProductOV('Prod1', 'test1', 100)->toArray(),
            new ProductOV('Prod2', 'test2', 100)->toArray(),
            new ProductOV('Prod3', 'test3', 100)->toArray(),
        ]);
    }
}
