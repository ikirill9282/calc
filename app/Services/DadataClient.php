<?php

namespace App\Services;

use Dadata\DadataClient as BaseClient;
use Illuminate\Support\Facades\Log;

class DadataClient
{
  private ?string $api_key = null;
  private ?string $api_secret = null;
  protected ?BaseClient $client = null;

  public function __construct()
  {
    $this->api_key = config('services.dadata.api_key');
    $this->api_secret = config('services.dadata.secret');

    if (!is_string($this->api_key) || !is_string($this->api_secret) || $this->api_key === '' || $this->api_secret === '') {
      return;
    }

    $this->client = new BaseClient($this->api_key, $this->api_secret);
  }
  

  public function __call($name, $arguments)
  {
    if ($this->client && method_exists($this->client, $name)) {
      try {
        return $this->client->$name(...$arguments);
      } catch (\Throwable $e) {
        Log::warning('DaData request failed', [
          'method' => $name,
          'message' => $e->getMessage(),
        ]);

        return [];
      }
    }

    return [];
  }
}
