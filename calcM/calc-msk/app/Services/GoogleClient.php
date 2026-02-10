<?php

namespace App\Services;


use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleClient
{
  public static function write(array $data, bool $forceUpdate = false)
  {
    $client = new Client();
    $client->setApplicationName(env('APP_NAME'));
    $client->setScopes([Sheets::SPREADSHEETS]);
    $client->setAuthConfig(Storage::disk('local')->json('credentials.json'));
    $client->setAccessType('offline');

    $service = new Sheets($client);

    $spreadsheetId = '1LDtQ6iJ8BE9uMUHJGySWdKG6FqTuPzYzET7KaLGPGCQ';
    $sheetName = 'Лист1';

    $response = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
    $existing = $response->getValues() ?? [];

    $order_ids = array_column($existing, 1);
    $order_id = $data[1];
    $rowIndex = array_search($order_id, $order_ids);

    // Если заявка уже существует и нужно обновить
    if ($rowIndex !== false && $forceUpdate) {
      // rowIndex + 2 потому что: +1 для заголовка, +1 потому что индексы начинаются с 1 в Google Sheets
      $range = $sheetName . '!A' . ($rowIndex + 2);
      
      $body = new Sheets\ValueRange([
        'values' => [$data]
      ]);
      
      $params = [
        'valueInputOption' => 'USER_ENTERED',
      ];

      $result = $service->spreadsheets_values->update(
        $spreadsheetId,
        $range,
        $body,
        $params
      );
      Log::debug("Order updated {$order_id}", ['order' => $data, 'result' => $result]);
      return;
    }

    // Если заявки еще нет, добавляем новую
    if ($rowIndex === false) {
      $body = new Sheets\ValueRange([
        'values' => [$data]
      ]);
      
      $params = [
        'valueInputOption' => 'USER_ENTERED',
      ];

      $result = $service->spreadsheets_values->append(
        $spreadsheetId,
        $sheetName,
        $body,
        $params
      );
      Log::debug("Order printed {$order_id}", ['order' => $data, 'result' => $result]);
    }
  }
}
