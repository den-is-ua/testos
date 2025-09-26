<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



class AMQSender  
{
    const CONFIRM_IMPORT = 'import_confirmations';


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

    public function sendConfirmImport(int $importId)
    {
        $connection = $this->AMQPStreamConnection;
        DB::afterCommit(function() use ($connection,  $importId) {
            $channel = $connection->channel();
            $channel->queue_declare(AMQSender::CONFIRM_IMPORT, false, true, false, false);


            $messageBody = json_encode([
                'import_id' => $importId,
            ]);

            $message = new AMQPMessage($messageBody, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // Make message durable
            ]);

            $channel->basic_publish($message, '', AMQSender::CONFIRM_IMPORT);

            $channel->close();
        });
    }
}
