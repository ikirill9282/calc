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
    $order_id = $data[1];

    // Check existing order id
    if (!in_array($order_id, $order_ids)) {
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
      Log::debug("Order printed {$order_id}", ['order' => $data, 'result' => $result]);
      
      // После создания заявки читаем данные обратно из Google Sheets
      // чтобы получить send_date из колонки H
      // Добавляем небольшую задержку, чтобы дать время формулам в Google Sheets обновиться
      sleep(1);
      self::syncSendDateFromSheet($service, $spreadsheetId, $sheetName, $order_id);
    } else {
      Order::find($order_id)->print()->firstOrCreate();
    }
  }

  /**
   * Синхронизирует send_date из колонки H Google Sheets в заявку
   */
  protected static function syncSendDateFromSheet($service, $spreadsheetId, $sheetName, $orderId)
  {
    try {
      // Делаем несколько попыток с задержкой, так как формулы в Google Sheets могут обновляться не сразу
      $maxAttempts = 3;
      $attempt = 0;
      
      while ($attempt < $maxAttempts) {
        // Получаем все данные из таблицы
        $response = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
        $rows = $response->getValues() ?? [];
        
        // Находим строку с нужной заявкой (order_id находится в колонке B, индекс 1)
        foreach ($rows as $index => $row) {
          if (isset($row[1]) && $row[1] == $orderId) {
            // Колонка H - это индекс 7 (0-based: A=0, B=1, C=2, D=3, E=4, F=5, G=6, H=7)
            if (isset($row[7]) && !empty(trim($row[7]))) {
              $sendDateValue = trim($row[7]);
              
              // Пропускаем, если это формула или пустое значение
              if (strpos($sendDateValue, '=') === 0) {
                // Это формула, нужно подождать еще
                if ($attempt < $maxAttempts - 1) {
                  sleep(1);
                  $attempt++;
                  continue 2; // Продолжаем внешний цикл while
                }
                break; // Если это последняя попытка, выходим
              }
              
              // Парсим дату из Google Sheets
              try {
                $sendDate = \Carbon\Carbon::parse($sendDateValue);
                
                // Обновляем заявку
                $order = Order::find($orderId);
                if ($order) {
                  $newSendDate = $sendDate->toDateString();
                  if ($order->send_date != $newSendDate) {
                    $order->send_date = $newSendDate;
                    $order->save();
                    
                    Log::info("Order send_date synced from Google Sheets", [
                      'order_id' => $orderId,
                      'send_date' => $newSendDate,
                      'row_index' => $index,
                      'attempt' => $attempt + 1,
                    ]);
                  }
                }
                return; // Успешно синхронизировали, выходим
              } catch (\Exception $e) {
                Log::warning("Failed to parse send_date from Google Sheets", [
                  'order_id' => $orderId,
                  'value' => $sendDateValue,
                  'error' => $e->getMessage(),
                  'attempt' => $attempt + 1,
                ]);
              }
            }
            break;
          }
        }
        
        // Если не нашли данные, делаем еще одну попытку
        if ($attempt < $maxAttempts - 1) {
          sleep(1);
        }
        $attempt++;
      }
      
      Log::warning("Failed to sync send_date from Google Sheets after {$maxAttempts} attempts", [
        'order_id' => $orderId,
      ]);
    } catch (\Exception $e) {
      Log::error("Failed to sync send_date from Google Sheets", [
        'order_id' => $orderId,
        'error' => $e->getMessage(),
      ]);
    }
  }
}
