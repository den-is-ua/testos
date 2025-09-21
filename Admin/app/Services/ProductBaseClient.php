<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;



class ProductBaseClient  
{
    public function __construct(private string $host, private string $key)
    {
        if (empty($host) || empty($key)) {
            throw new Exception('Need to setup environment: PRODUCT_BASE_HOST and PRODUCT_BASE_KEY');
        }

        $this->host = Str::finish($host, '/');
    }

    public static function autoapplyConfigs()
    {
        return new self(config('clients.product_base_host'), config('clients.product_base_key'));
    }

    public function getProducts($page = 1, $perPage = 10, $filter = '')
    {
        $response = Http::withToken($this->key)->get($this->host . 'api/products', [
            'page' => $page,
            'per_page' => $perPage,
            'filter' => $filter
        ]);

        $this->throwExceptionIfResponseFailed(__METHOD__, $response);
        
        return $response;
    }

    private function throwExceptionIfResponseFailed(string $method, Response $response)
    {
        if ($response->serverError()) {
            throw new Exception(__CLASS__ . $method .  ' Response failed: ' . json_encode($response));
        }
    }
}
