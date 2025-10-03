<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Pusher\Pusher;
use Throwable;

class AMQRecieveImportProgress extends Command
{
    protected $signature = 'amq:recieve-import-progress';

    public function handle()
    {
        $host = config('rebbitmq.host');
        $port = config('rebbitmq.port');
        $user = config('rebbitmq.user');
        $pass = config('rebbitmq.password');
        $vhost = '/';

        $connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $channel = $connection->channel();

        $queue = 'import_progress';

        $channel->queue_declare($queue, false, true, false, false);

        // fair dispatch: at most 10 unacked messages per worker
        $channel->basic_qos(null, 10, null);

        $callback = function (AMQPMessage $msg) {
            try {
                $data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

                Log::debug(__CLASS__ . __METHOD__ . '. Recieved data', ['data.first_element' => reset($data)]);

                // (optional) validate schema/version
                validator($data, [
                    'id' => 'required|integer',
                    'file_name' => 'required|string',
                    'progress' => 'required|integer',
                    'completed' => 'required|boolean',
                ])->validate();

                $pusher = new Pusher(
                    config('broadcasting.connections.pusher.key'),
                    config('broadcasting.connections.pusher.secret'),
                    config('broadcasting.connections.pusher.app_id'),
                    ['cluster' => config('broadcasting.connections.pusher.options.cluster')]
                );

                $pusher->trigger('import-progress', 'updated-progress', $data);

                $msg->ack();

                Log::debug(__CLASS__ . __METHOD__ . '. Acknowlaged importId ' . $data['id']);
                $this->info('Progress recieved: ' . $data['progress']);
            } catch (Throwable $e) {
                Log::error($e->getMessage(), $e->getTrace());
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
