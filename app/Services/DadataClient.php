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
    $this->api_key = $this->resolveValue([
      config('services.dadata.api_key'),
      config('services.dadata.token'),
      env('DADATA_API'),
      env('DADATA_API_KEY'),
      env('DADATA_TOKEN'),
    ]);

    // Для suggest API ключа достаточно, secret может отсутствовать.
    $this->api_secret = $this->resolveValue([
      config('services.dadata.secret'),
      config('services.dadata.secret_key'),
      env('DADATA_SECRET'),
      env('DADATA_SECRET_KEY'),
    ]);

    if (!is_string($this->api_key) || $this->api_key === '') {
      Log::warning('DaData client disabled: API key is missing');
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

  /**
   * @param array<mixed> $values
   */
  protected function resolveValue(array $values): ?string
  {
    foreach ($values as $value) {
      if (!is_string($value)) {
        continue;
      }

      $trimmed = trim($value);
      if ($trimmed !== '') {
        return $trimmed;
      }
    }

    return null;
  }
}
