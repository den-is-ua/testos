<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;



class ImportClient  
{
    public function __construct(private string $host, private string $key)
    {
        if (empty($host) || empty($key)) {
            throw new Exception('Need to setup environment: IMPORT_HOST and IMPORT_KEY');
        }

        $this->host = Str::finish($host, '/');
    }

    public static function autoapplyConfigs()
    {
        return new self(config('clients.import_host'), config('clients.import_key'));
    }

    public function importFile(UploadedFile $file)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($this->key)
            ->attach('file', $file->get(), $file->getFilename())
            ->post($this->host . 'api/imports');

        $this->throwExceptionIfResponseFailed(__METHOD__, $response);

        return $response;
    }

    public function healthcheck()
    {
        return Http::withToken($this->key)->get($this->host . 'api/hc');
    }

    private function throwExceptionIfResponseFailed(string $method, Response $response)
    {
        if ($response->serverError()) {
            throw new Exception(__CLASS__ . $method .  ' Response failed: ' . json_encode($response));
        }
    }
}
