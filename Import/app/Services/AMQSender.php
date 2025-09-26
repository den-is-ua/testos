<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\ImportResource;
use App\Models\Import;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



class AMQSender 
{
    const IMPORTS_QUEUE = 'imports';
    const IMPORT_PROGRESS = 'import_progress';


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

    public function sendImportProgress(Import $import)
    {
        $connection = $this->AMQPStreamConnection;
        DB::afterCommit(function() use ($connection, $import) {
            $channel = $connection->channel();
            $channel->queue_declare(AMQSender::IMPORT_PROGRESS, false, true, false, false);


            $messageBody = json_encode(new ImportResource($import));

            $message = new AMQPMessage($messageBody, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // Make message durable
            ]);

            $channel->basic_publish($message, '', AMQSender::IMPORT_PROGRESS);

            $channel->close();
        });
    }
}
