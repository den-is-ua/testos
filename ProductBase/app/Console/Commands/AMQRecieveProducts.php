<?php

namespace App\Console\Commands;

use App\Services\AMQSender;
use App\Services\UpsertProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class AMQRecieveProducts extends Command
{
    protected $signature = 'amq:recieve-products';

    public function handle(AMQSender $AMQSender)
    {
        $host = config('queue.connections.rabbitmq.hosts.0.host');
        $port = config('queue.connections.rabbitmq.hosts.0.port');
        $user = config('queue.connections.rabbitmq.hosts.0.user');
        $pass = config('queue.connections.rabbitmq.hosts.0.password');
        $vhost = '/';

        $connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $channel = $connection->channel();

        $queue = 'imports';

        $channel->queue_declare($queue, false, true, false, false);

        // fair dispatch: at most 10 unacked messages per worker
        $channel->basic_qos(null, 10, null);

        $callback = function (AMQPMessage $msg) use ($AMQSender) {
            try {
                $data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

                Log::debug(__CLASS__ . __METHOD__ . '. Recieved data', ['data.first_element' => reset($data)]);

                // (optional) validate schema/version
                validator($data, [
                    'import_id' => 'required|integer',
                    'products' => 'required|array',
                    'products.*.name' => 'required|string',
                    'products.*.sku' => 'required|string',
                    'products.*.price' => 'required|numeric',
                    'products.*.category' => 'nullable|string',
                    'products.*.description' => 'nullable|string',
                    'products.*.images' => 'nullable|array',
                ])->validate();

                // Do your business logic here
                UpsertProductService::upsert($data['products']);
                $AMQSender->sendConfirmImport($data['import_id']);

                $msg->ack();

                Log::debug(__CLASS__ . __METHOD__ . '. Acknowlaged importId ' . $data['import_id']);
                $this->info('Data Upserted import_id: ' . $data['import_id']);
            } catch (Throwable $e) {
                // requeue=false sends to DLX if configured; otherwise drops or keeps unacked
                $this->error($e->getMessage());
                $msg->reject(false);
            }
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return self::SUCCESS;
    }
}
