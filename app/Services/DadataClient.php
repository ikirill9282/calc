<?php

namespace App\Services;

use App\Services\Dadata\Router;
use App\Services\Dadata\Client;
use Dadata\DadataClient as BaseClient;

class DadataClient
{
  private string $api_key;
  private string $api_secret;
  protected BaseClient $client;

  public function __construct()
  {
    $this->api_key = env('DADATA_API');
    $this->api_secret = env('DADATA_SECRET');

    $this->client = new BaseClient($this->api_key, $this->api_secret);
  }
  

  public function __call($name, $arguments)
  {
    if (method_exists($this->client, $name)) {
      try {
        return $this->client->$name(...$arguments);
      } catch(\Exception $e) {
        return [];
      }
    }
  }
}