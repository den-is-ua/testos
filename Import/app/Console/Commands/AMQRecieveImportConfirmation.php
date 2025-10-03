<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Services\AMQSender;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class AMQRecieveImportConfirmation extends Command
{
    protected $signature = 'amq:recieve-import-confirmations';

    public function handle(AMQSender $AMQSender)
    {
        $host = config('queue.connections.rabbitmq.hosts.0.host');
        $port = config('queue.connections.rabbitmq.hosts.0.port');
        $user = config('queue.connections.rabbitmq.hosts.0.user');
        $pass = config('queue.connections.rabbitmq.hosts.0.password');
        $vhost = '/';

        $connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $channel = $connection->channel();

        $queue = 'import_confirmations';

        $channel->queue_declare($queue, false, true, false, false);

        // fair dispatch: at most 10 unacked messages per worker
        $channel->basic_qos(null, 10, null);

        $callback = function (AMQPMessage $msg) use ($AMQSender) {
            try {
                $data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

                // (optional) validate schema/version
                validator($data, [
                    'import_id' => 'required|integer',
                ])->validate();

                $import = Import::query()->findOrFail($data['import_id']);
                $import->increment('confirmed_iterations');
                if ($import->confirmed_iterations == $import->total_iterations) {
                    $import->completed_at = now();
                    $import->save();
                }

                $import->refresh();
                $AMQSender->sendImportProgress($import);

                $msg->ack();

                $this->info('Import updated: ' . $data['import_id']);
            } catch (Throwable $e) {
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
