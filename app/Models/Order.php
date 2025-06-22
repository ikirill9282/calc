<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Revolution\Google\Sheets\Facades\Sheets;

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
    $sid = '1ZOkCAKId9W5nAQya3ZFC1GyYeeGM-Mbne7U-F44Zw-E';
    $user = User::find($this->user_id);
    $agent = Agent::find($this->agent_id);

    $user_data = $user->toArray();
    $user_data['verified'] = is_null($user->email_verified_at) ? 'false' : 'true';
    unset($user_data['id'], $user_data['created_at'], $user_data['updated_at'], $user_data['email_verified_at']);

    $order_data = $this->toArray();
    $order_data['transfer_method'] = $this->getTransferMethod();
    $order_data['payment_method'] = $this->getPaymentMethodLabel();
    unset($order_data['user'], $order_data['id'], $order_data['user_id'], $order_data['agent_id']);

    $agent_data = $agent->toArray();
    unset($agent_data['id'], $agent_data['user_id'], $agent_data['created_at'], $agent_data['updated_at']);

    $item = array_merge($user_data, $order_data, $agent_data);
    $item = array_merge(['order_id' => $this->id], $item);
    $data = [$item];
    $data2 = [array_values($item)];
    
    // dd($data);
    $sheet = Sheets::spreadsheet($sid)
      ->sheet('Лист2');

    $int = ($this->id - 100500 + 2);

    $sheet->range("A1")->append(array_keys($item));
    dd('ok');
    if (!$this->print()->exists()) {
      $sheet->range("A$int")->append($data);
      $this->print()->create();
    } 
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
      $this->setAttribute($field, $value);
    }
  }

  public static function prepareOrder(array $fields): static
  {
    $order = new static();
    $order->fillFields($fields);

    return $order;
  }

  public function getPaymentMethodLabel(): string
  {
    return match($this->payment_method) {
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
