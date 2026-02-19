<?php

namespace App\Services;

use Dadata\DadataClient as BaseClient;
use Illuminate\Support\Facades\Log;

class DadataClient
{
  private ?string $api_key = null;
  private ?string $api_secret = null;
  protected ?BaseClient $client = null;
  protected static ?array $envFileCache = null;

  public function __construct()
  {
    $this->api_key = $this->resolveValue([
      config('services.dadata.api_key'),
      config('services.dadata.token'),
      $this->resolveRuntimeEnvValue(['DADATA_API', 'DADATA_API_KEY', 'DADATA_TOKEN']),
      $this->resolveEnvFileValue(['DADATA_API', 'DADATA_API_KEY', 'DADATA_TOKEN']),
    ]);

    // Для suggest API ключа достаточно, secret может отсутствовать.
    $this->api_secret = $this->resolveValue([
      config('services.dadata.secret'),
      config('services.dadata.secret_key'),
      $this->resolveRuntimeEnvValue(['DADATA_SECRET', 'DADATA_SECRET_KEY']),
      $this->resolveEnvFileValue(['DADATA_SECRET', 'DADATA_SECRET_KEY']),
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

  /**
   * @param array<int, string> $keys
   */
  protected function resolveRuntimeEnvValue(array $keys): ?string
  {
    foreach ($keys as $key) {
      $value = getenv($key);
      if (!is_string($value) || trim($value) === '') {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
      }

      if (is_string($value) && trim($value) !== '') {
        return trim($value);
      }
    }

    return null;
  }

  /**
   * @param array<int, string> $keys
   */
  protected function resolveEnvFileValue(array $keys): ?string
  {
    $values = $this->readEnvFileValues();

    foreach ($keys as $key) {
      if (!array_key_exists($key, $values)) {
        continue;
      }

      $value = trim((string) $values[$key]);
      if ($value !== '') {
        return $value;
      }
    }

    return null;
  }

  /**
   * @return array<string, string>
   */
  protected function readEnvFileValues(): array
  {
    if (is_array(self::$envFileCache)) {
      return self::$envFileCache;
    }

    $path = base_path('.env');
    if (!is_file($path) || !is_readable($path)) {
      self::$envFileCache = [];
      return self::$envFileCache;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
      self::$envFileCache = [];
      return self::$envFileCache;
    }

    $result = [];

    foreach ($lines as $line) {
      $line = trim((string) $line);
      if ($line === '' || str_starts_with($line, '#')) {
        continue;
      }

      if (str_starts_with($line, 'export ')) {
        $line = trim(substr($line, 7));
      }

      $parts = explode('=', $line, 2);
      if (count($parts) !== 2) {
        continue;
      }

      $name = trim($parts[0]);
      $value = trim($parts[1]);
      if ($name === '') {
        continue;
      }

      if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
        $value = substr($value, 1, -1);
      } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
        $value = substr($value, 1, -1);
      } else {
        // Убираем inline-комментарии только для незакавыченных значений.
        $value = trim(explode(' #', $value, 2)[0]);
      }

      $result[$name] = $value;
    }

    self::$envFileCache = $result;

    return self::$envFileCache;
  }
}
