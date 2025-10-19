<?php

namespace App\Services;


use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;

class GoogleClient
{
  public static function write(array $data)
  {
    $client = new Client();
    $client->setApplicationName(env('APP_NAME'));
    $client->setScopes([Sheets::SPREADSHEETS]);
    $client->setAuthConfig(Storage::disk('local')->json('credentials.json'));
    $client->setAccessType('offline');

    $service = new Sheets($client);

    $spreadsheetId = '1RCZPm9Q-A-1osteZkMlwYYCkuLJ0em1zKpKZGOnC6is';
    $sheetName = 'Лист1';

    $response = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
    $existing = $response->getValues() ?? [];

    $order_ids = array_column($existing, 1);

    // Check existing order id
    if (!in_array($data[1], $order_ids)) {
      $body = new Sheets\ValueRange([
        'values' => [$data]
      ]);
      
      $params = [
        'valueInputOption' => 'USER_ENTERED',
        // 'insertDataOption' => 'INSERT_ROWS'
      ];

      $result = $service->spreadsheets_values->append(
        $spreadsheetId,
        $sheetName,
        $body,
        $params
      );
      Log::debug("Order wtited {$data[1]}", ['order' => $data, 'result' => $result]);
    } else {
      Order::find($data[1])->print()->firstOrCreate();
    }
  }
}
