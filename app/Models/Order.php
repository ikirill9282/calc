<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Revolution\Google\Sheets\Facades\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;

class Order extends Model
{

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function print()
  {
    return $this->hasOne(OrderPrint::class);
  }

  public function writeSheet()
  {
    $user = User::find($this->user_id);
    $agent = Agent::where('id', $this->agent_id)->first();

    $user_data = $user->toArray();
    $user_data['verified'] = is_null($user->email_verified_at) ? 'false' : 'true';
    unset($user_data['id'], $user_data['created_at'], $user_data['updated_at'], $user_data['email_verified_at']);

    $order_data = $this->toArray();
    $order_data['transfer_method'] = $this->getTransferMethod();
    $order_data['payment_method'] = $this->getPaymentMethodLabel($this->payment_method);
    $order_data['payment_method_pick'] = $this->getPaymentMethodLabel($this->payment_method_pick);
    unset($order_data['user'], $order_data['id'], $order_data['user_id'], $order_data['agent_id']);

    if ($this->transfer_method == 'pick') {
      $order_data['transfer_method_receive_date'] = '';
    } elseif ($this->transfer_method == 'receive') {
      $order_data['transfer_method_pick_address'] = '';
      $order_data['transfer_method_pick_date'] = '';
    }
// ->toIso8601String()
    if (!empty($order_data['delivery_date'])) {
      $order_data['delivery_date'] = Carbon::parse($order_data['delivery_date'])->format('d.m.Y');
    }

    if (!empty($order_data['post_date'])) {
      $order_data['post_date'] = Carbon::parse($order_data['post_date'])->format('d.m.Y');
    }

    if (!empty($order_data['transfer_method_receive_date'])) {
      $order_data['transfer_method_receive_date'] = Carbon::parse($order_data['transfer_method_receive_date'])->format('d.m.Y');
    }

    if (!empty($order_data['transfer_method_pick_date'])) {
      $order_data['transfer_method_pick_date'] = Carbon::parse($order_data['transfer_method_pick_date'])->format('d.m.Y');
    }

    if (!empty($order_data['created_at'])) {
      $order_data['created_at'] = Carbon::parse($order_data['created_at'])->tz('Europe/Moscow')->format('d.m.Y H:i:s');
    }

    if (!empty($order_data['updated_at'])) {
      $order_data['updated_at'] = Carbon::parse($order_data['updated_at'])->tz('Europe/Moscow')->format('d.m.Y H:i:s');
    }

    $order_data['palletizing_type'] = match($order_data['palletizing_type']) {
      'single' => 'Палетирование',
      'pallet' => 'Поддон и палетирование',
      default => null,
    };

    $agent_data = $agent->toArray();
    unset($agent_data['id'], $agent_data['user_id'], $agent_data['created_at'], $agent_data['updated_at']);

    $item = array_merge($user_data, $order_data, $agent_data);
    $item = array_merge(['order_id' => $this->id], $item);
    $item = array_map(fn($val) => is_null($val) ? '' : $val, $item);

    $int = ($this->id - 100500 + 2);
    $range = "A$int:AI$int";

    // $sid = '1ZOkCAKId9W5nAQya3ZFC1GyYeeGM-Mbne7U-F44Zw-E';
    $sids = [
      1 => '1j6TkvE3ocDSQXP9ECKQ0MsJeXk2hYvgoTXgYkQIbh9I',
      2 => '1mXYqtlmxfe7qr_hnAJOjdSuy_NmjO9Fu0CrxTvkG4C4',
      3 => '1DGdOmjC0ItxX22ynwVnVTi-7VDHcpK11wtmJv7cICQI',
    ];

    $sid = null;
    switch(true) {
      case str_contains(mb_strtolower($item['warehouse_id']), 'симферополь'):
        $sid = 1;
        break;
      case str_contains(mb_strtolower($item['warehouse_id']), 'ростов-на-дону'):
        $sid = 2;
        break;
      case str_contains(mb_strtolower($item['warehouse_id']), 'москва'):
        $sid = 3;
        break;
    }

    $sid = $sids[$sid];
    // $sid = str_contains(mb_strtolower($item['warehouse_id']), 'симферополь') 
    //   ? '1j6TkvE3ocDSQXP9ECKQ0MsJeXk2hYvgoTXgYkQIbh9I'
    //   : '1mXYqtlmxfe7qr_hnAJOjdSuy_NmjO9Fu0CrxTvkG4C4';

    $sheet = Sheets::spreadsheet($sid)
      ->sheet("Лист1")
      ->range('')
      ;

    $formatted = [
      'num' => '=СТРОКА()-1',
      'order_id' => $item['order_id'],
      'created_at' => $item['created_at'],
      'agent' => $item['title'],
      'agent_name' => $agent->name,
      'agent_phone' => "'$agent->phone",
      'delivery_date' => $item['delivery_date'],
      'distrubutor_id' => $item['distributor_center_id'] . ' ' . $item['distributor_id'],
      'payment_method' => $item['payment_method'],
      'custom1' => null,
      'custom2' => null,
      'custom3' => null,
      'custom4' => null,
      'pallets_count' => $item['pallets_count'],
      'custom5' => null,
      'boxes_count' => $item['boxes_count'],
      'custom6' => null,
      'fn1' => null,
      'fn2' => null,
      'fn3' => null,
      'custom7' => null,
      'boxes_volume' => $item['boxes_volume'],
      'custom8' => null,
      'boxes_weight' => $item['boxes_weight'],
      'palletizing_type' => empty($item['palletizing_type']) ? 'Нет' : 'Да',
      'transfer_method_pick' => match($this->transfer_method) {
        'receive' => 'Нет',
        'pick' => 'Да',
      },
      'payment_method_pick' => $item['payment_method_pick'],
      'pick_date' => $item['transfer_method_pick_date'],
      'pick_address' => $item['transfer_method_pick_address'],
      'comment' => $item['cargo_comment'],
      'agent_mail' => $agent->email,
      'inn' => $item['inn'],
      'ogrn' => $item['ogrn'],
      'user_name' => $user->name,
      'user_phone' => "'$user->phone",
      'user_email' => $user->email,
    ];

    $formatted = array_map(fn($val) => is_null($val) ? '' : $val, $formatted);
    
    $values = [
      array_values($formatted),
    ];

    // dd($values, [array_values($formatted)]);

    // if (!$this->print()->exists()) {
      $sheet->append($values, 'USER_ENTERED');
      // $this->print()->firstOrCreate();
    // } else {
      // $sheet->range($range)->update($values);
    // }
  }

  public function getCity()
  {
    return SheetData::query()
      ->where(DB::raw("CONCAT(wh, ' ', wh_address)"), '=', $this->warehouse_id)
      ->select('wh')
      ->distinct()
      // ->ddRawSql()
      ->first()
      ?->wh ?? ''
      ;
      
  }

  public function fillFields(array $fields): void
  {
    // dd($fields);
    foreach ($fields as $field => $value) {
      if (is_array($value)) {
        foreach ($value as $key => $val) {
          $k = $field.'_'.$key;
          if ($field == 'boxes_data') {
            $k = 'boxes_'.$key;
          }
          if ($field == 'pallets_data') {
            $k = 'pallets_'.$key;
          }
          $this->setAttribute($k, $val);
        }
        continue;
      }

      try {
        $this->setAttribute($field, $value);
      } catch (\Exception $e) {
        // dd($fields, $field, $value, $e->getMessage());
      } catch (\Error $e) {
        // dd($fields, $field, $value, $e->getMessage());
      };
    }
  }

  public static function prepareOrder(array $fields): static
  {
    $order = new static();
    $order->fillFields($fields);

    return $order;
  }

  public function getPaymentMethodLabel($val): string
  {
    return match($val) {
      'cash' => 'Наличными при отправке',
      'bill' => 'По счету',
    };
  }

  public function getTransferMethod(): string
  {
    return match($this->transfer_method) {
      'receive' => 'Принять на складе',
      'pick' => 'Забрать по адресу',
    };
  }

  public function deliveryDate(): Attribute
  {
    return Attribute::make(
      set: fn($val) => Carbon::parse($val)->format('Y-m-d H:i:s'),
    );
  }

  public function transferMethodReceiveDate(): Attribute
  {
    return Attribute::make(
      set: fn($val) => Carbon::parse($val)->format('Y-m-d H:i:s')
    );
  }

  public function transferMethodPickDate(): Attribute
  {
    return Attribute::make(
      set: fn($val) => Carbon::parse($val)->format('Y-m-d H:i:s')
    );
  }
}
