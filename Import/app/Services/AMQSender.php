<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



class AMQSender 
{
    const IMPORTS_QUEUE = 'imports';


    private AMQPStreamConnection $AMQPStreamConnection;

    public function __construct()
    {
        $this->AMQPStreamConnection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'), 
            env("RABBITMQ_PORT"), 
            env('RABBITMQ_USER'), 
            env('RABBITMQ_PASSWORD')
        );
    }

    public function __destruct()
    {
        $this->AMQPStreamConnection->close();
    }

    public function sendProducts(int $importId, array $products)
    {
        $connection = $this->AMQPStreamConnection;
        DB::afterCommit(function() use ($connection,  $importId, $products) {
            $channel = $connection->channel();
            $channel->queue_declare(AMQSender::IMPORTS_QUEUE, false, true, false, false);


            $messageBody = json_encode([
                'import_id' => $importId,
                'products' => $products
            ]);

            $message = new AMQPMessage($messageBody, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // Make message durable
            ]);

            $channel->basic_publish($message, '', AMQSender::IMPORTS_QUEUE);

            $channel->close();
        });
    }
}
