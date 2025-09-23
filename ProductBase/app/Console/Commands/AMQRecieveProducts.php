<?php

namespace App\Console\Commands;

use App\Services\UpsertProductService;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQRecieveProducts extends Command
{
    protected $signature = 'amq:recieve-products';


    public function handle()
    {
        $host  = env('RABBITMQ_HOST');
        $port  = (int) env('RABBITMQ_PORT');
        $user  = env('RABBITMQ_USER');
        $pass  = env('RABBITMQ_PASSWORD');
        $vhost = '/';

        $connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $channel    = $connection->channel();

        $queue      = 'imports';

        $channel->queue_declare($queue, false, true, false, false);

        // fair dispatch: at most 10 unacked messages per worker
        $channel->basic_qos(null, 10, null);

        $callback = function (AMQPMessage $msg) {
            try {
                $data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

                // (optional) validate schema/version
                validator($data, [
                    'import_id'            => 'required|integer',
                    'products'            => 'required|array',
                    'products.*.name'     => 'required|string',
                    'products.*.sku'      => 'required|string',
                    'products.*.price'    => 'required|numeric',
                    'products.*.category' => 'nullable|string',
                    'products.*.description' => 'nullable|string',
                    'products.*.images'   => 'nullable|array',
                ])->validate();

                // Do your business logic here
                UpsertProductService::upsert($data['products']);

                $msg->ack();

                $this->info('Data Upserted import_id: ' . $data['import_id']);
            } catch (\Throwable $e) {
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
