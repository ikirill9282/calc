<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Revolution\Google\Sheets\Facades\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;

class Order extends Model
{
  public const FIELD_LABELS = [
    'id' => '№ заявки',
    'created_at' => 'Дата создания',
    'updated_at' => 'Дата изменения',
    'user_id' => 'Пользователь',
    'agent_id' => 'Отправитель',
    'delivery_date' => 'Дата поставки на РЦ',
    'post_date' => 'Дата публикации',
    'warehouse_id' => 'Склад',
    'distributor_id' => 'РЦ',
    'distributor_center_id' => 'Адрес РЦ',
    'payment_method' => 'Способ оплаты',
    'payment_method_pick' => 'Оплата за забор',
    'individual' => 'Индивидуальный расчет',
    'cargo' => 'Тип груза',
    'cargo_type' => 'Описание груза',
    'boxes_count' => 'Кол-во коробов',
    'boxes_weight' => 'Вес коробов, кг',
    'boxes_volume' => 'Объем коробов, м³',
    'pallets_count' => 'Кол-во палет',
    'pallets_boxcount' => 'Коробов в палете',
    'pallets_weight' => 'Вес палет, кг',
    'pallets_volume' => 'Объем палет, м³',
    'palletizing_type' => 'Тип палетирования',
    'palletizing_count' => 'Палетирование кол-во',
    'has_pickup' => 'Забор груза',
    'transfer_method' => 'Способ передачи',
    'transfer_method_receive_date' => 'Дата привоза клиентом',
    'transfer_method_pick_date' => 'Дата забора груза',
    'transfer_method_pick_address' => 'Адрес забора',
    'pick' => 'Оплата за забор, ₽',
    'delivery' => 'Доставка, ₽',
    'additional' => 'Палетирование, ₽',
    'total' => 'Предварительная сумма, ₽',
    'cargo_comment' => 'Комментарий',
    'highlight_color' => 'Цвет подсветки',
  ];

  protected $casts = [
    'changed_fields' => 'array',
    'highlight_color' => 'string',
  ];

  public static function boot()
  {
    parent::boot();

    static::saving(function($model) {
      if ($model->transfer_method == 'pick') {
        $model->transfer_method_receive_date = null;
      } elseif ($model->transfer_method == 'receive') {
        $model->transfer_method_pick_address = null;
        $model->transfer_method_pick_date = null;
        $model->payment_method_pick = null;
      }

      if ($model->cargo == 'boxes') {
        $model->pallets_count = 0;
      } elseif ($model->cargo == 'pallets') {
        $model->boxes_count = 0;
        $model->boxes_weight = 0;
        $model->boxes_volume = 0;
      }

      if ($model->individual) {
        $model->pick = null;
        $model->delivery = null;
        $model->additional = null;
        $model->total = null;
      }

      if (!$model->exists) {
        return;
      }

      $dirty = array_keys($model->getDirty());
      $ignore = ['created_at', 'updated_at', 'changed_fields'];
      $changed = array_values(array_diff($dirty, $ignore));

      $previous = $model->getOriginal('changed_fields') ?? [];
      if (!is_array($previous)) {
        $previous = (array) $previous;
      }

      $merged = array_values(array_unique(array_merge($previous, $changed)));

      if (!empty($merged)) {
        $model->changed_fields = $merged;
      } elseif (!empty($previous)) {
        $model->changed_fields = $previous;
      } else {
        $model->changed_fields = null;
      }
    });
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function print()
  {
    return $this->hasOne(OrderPrint::class);
  }
	public function agent()
	{
			return $this->belongsTo(Agent::class);
	}

  public function hasChanged(string ...$fields): bool
  {
    if (empty($this->changed_fields)) {
      return false;
    }

    foreach ($fields as $field) {
      if (in_array($field, $this->changed_fields, true)) {
        return true;
      }
    }

    return false;
  }

  public function distributionLabel(): string
  {
    $parts = array_filter([
      $this->distributor_id,
      $this->distributor_center_id,
    ], fn ($value) => filled($value));

    return implode(' - ', $parts);
  }

  public function getDistributionLabelAttribute(): string
  {
    return $this->distributionLabel();
  }

  public static function getFieldLabel(string $field): string
  {
    if (array_key_exists($field, self::FIELD_LABELS)) {
      return self::FIELD_LABELS[$field];
    }

    return Str::of($field)
      ->replace('_', ' ')
      ->squish()
      ->lower()
      ->ucfirst();
  }


  // public function writeSheet()
  // {
  //   if ($this->print()->exists()) {
  //     return ;
  //   }

  //   $user = User::find($this->user_id);
  //   $agent = Agent::where('id', $this->agent_id)->first();

  //   $user_data = $user->toArray();
  //   $user_data['verified'] = is_null($user->email_verified_at) ? 'false' : 'true';
  //   unset($user_data['id'], $user_data['created_at'], $user_data['updated_at'], $user_data['email_verified_at']);

  //   $order_data = $this->toArray();
  //   $order_data['transfer_method'] = $this->getTransferMethod();
  //   $order_data['payment_method'] = $this->getPaymentMethodLabel($this->payment_method);
  //   $order_data['payment_method_pick'] = $this->getPaymentMethodLabel($this->payment_method_pick);
  //   unset($order_data['user'], $order_data['id'], $order_data['user_id'], $order_data['agent_id']);

  //   if ($this->transfer_method == 'pick') {
  //     $order_data['transfer_method_receive_date'] = '';
  //   } elseif ($this->transfer_method == 'receive') {
  //     $order_data['transfer_method_pick_address'] = '';
  //     $order_data['transfer_method_pick_date'] = '';
  //   }

  //   if (!empty($order_data['delivery_date'])) {
  //     $order_data['delivery_date'] = Carbon::parse($order_data['delivery_date'])->format('d.m.Y');
  //   }

  //   if (!empty($order_data['post_date'])) {
  //     $order_data['post_date'] = Carbon::parse($order_data['post_date'])->format('d.m.Y');
  //   }

  //   if (!empty($order_data['transfer_method_receive_date'])) {
  //     $order_data['transfer_method_receive_date'] = Carbon::parse($order_data['transfer_method_receive_date'])->format('d.m.Y');
  //   }

  //   if (!empty($order_data['transfer_method_pick_date'])) {
  //     $order_data['transfer_method_pick_date'] = Carbon::parse($order_data['transfer_method_pick_date'])->format('d.m.Y');
  //   }

  //   if (!empty($order_data['created_at'])) {
  //     $order_data['created_at'] = Carbon::parse($order_data['created_at'])->tz('Europe/Moscow')->format('d.m.Y H:i:s');
  //   }

  //   if (!empty($order_data['updated_at'])) {
  //     $order_data['updated_at'] = Carbon::parse($order_data['updated_at'])->tz('Europe/Moscow')->format('d.m.Y H:i:s');
  //   }

  //   $order_data['palletizing_type'] = match($order_data['palletizing_type']) {
  //     'single' => 'Палетирование',
  //     'pallet' => 'Поддон и палетирование',
  //     default => null,
  //   };

  //   $agent_data = $agent->toArray();
  //   unset($agent_data['id'], $agent_data['user_id'], $agent_data['created_at'], $agent_data['updated_at']);

  //   $item = array_merge($user_data, $order_data, $agent_data);
  //   $item = array_merge(['order_id' => $this->id], $item);
  //   $item = array_map(fn($val) => is_null($val) ? '' : $val, $item);

  //   $sids = [
  //     1 => '1j6TkvE3ocDSQXP9ECKQ0MsJeXk2hYvgoTXgYkQIbh9I',
  //     2 => '1mXYqtlmxfe7qr_hnAJOjdSuy_NmjO9Fu0CrxTvkG4C4',
  //     3 => '1DGdOmjC0ItxX22ynwVnVTi-7VDHcpK11wtmJv7cICQI',
  //   ];

  //   $sid = null;
  //   switch(true) {
  //     case str_contains(mb_strtolower($item['warehouse_id']), 'симферополь'):
  //       $sid = 1;
  //       break;
  //     case str_contains(mb_strtolower($item['warehouse_id']), 'ростов-на-дону'):
  //       $sid = 2;
  //       break;
  //     case str_contains(mb_strtolower($item['warehouse_id']), 'москва'):
  //       $sid = 3;
  //       break;
  //   }

  //   $sid = $sids[$sid];

  //   $sheet = Sheets::spreadsheet($sid)
  //     ->sheet("Лист1")
  //     ->range('')
  //     ;

  //   $formatted = [
  //     'num' => '=СТРОКА()-1',
  //     'order_id' => $item['order_id'],
  //     'created_at' => $item['created_at'],
  //     'agent' => $item['title'],
  //     'agent_name' => $agent->name,
  //     'agent_phone' => "'$agent->phone",
  //     'delivery_date' => $item['delivery_date'],
  //     'distrubutor_id' => $item['distributor_id'],
  //     'distributor_center_id' => $item['distributor_center_id'],
  //     'payment_method' => $item['payment_method'],
  //     'individual' => $item['individual'] ? 'Да' : 'Нет',
  //     'custom1' => null,
  //     'custom2' => null,
  //     'custom3' => null,
  //     'custom4' => null,
  //     'cargo' => match($item['cargo']) {
  //       'boxes' => 'Коробки',
  //       'pallets' => 'Палеты',
  //     },
  //     'pallets_count' => $item['pallets_count'],
  //     'custom5' => null,
  //     'boxes_count' => $item['boxes_count'],
  //     'custom6' => null,
  //     'fn1' => null,
  //     'fn2' => null,
  //     'fn3' => null,
  //     'custom7' => null,
  //     'boxes_volume' => strval(floatval($item['boxes_volume'])),
  //     'custom8' => null,
  //     'boxes_weight' => strval(floatval($item['boxes_weight'])),
  //     'palletizing_type' => empty($item['palletizing_type']) ? 'Нет' : 'Да',
  //     'palletizing_count' => $item['palletizing_count'],
  //     'transfer_method_pick' => match($this->transfer_method) {
  //       'receive' => 'Нет',
  //       'pick' => 'Да',
  //     },
  //     'receive_date' => $item['transfer_method_receive_date'],
  //     'payment_method_pick' => $item['payment_method_pick'],
  //     'pick_date' => $item['transfer_method_pick_date'],
  //     'pick_address' => $item['transfer_method_pick_address'],
  //     'comment' => $item['cargo_comment'],
  //     'agent_mail' => $agent->email,
  //     'inn' => $item['inn'],
  //     'ogrn' => $item['ogrn'],
  //     'user_name' => $user->name,
  //     'user_phone' => "'$user->phone",
  //     'user_email' => $user->email,
  //   ];

  //   $formatted = array_map(fn($val) => is_null($val) ? '' : $val, $formatted);
    
  //   $values = [
  //     array_values($formatted),
  //   ];

    

    
  //     $sheet->append($values, 'USER_ENTERED');
  //     $this->print()->firstOrCreate();
  // }

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


  public function prepareSheetData()
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

    $formatted = [
      'num' => '=СТРОКА()-1',
      'order_id' => $item['order_id'],
      'created_at' => $item['created_at'],
      'agent' => $item['title'],
      'agent_name' => $agent->name,
      'agent_phone' => "'$agent->phone",
      'delivery_date' => $item['delivery_date'],
      'distrubutor_id' => $item['distributor_id'],
      'distributor_center_id' => $item['distributor_center_id'],
      'payment_method' => $item['payment_method'],
      'individual' => $item['individual'] ? 'Да' : 'Нет',
      'custom1' => null,
      'custom2' => null,
      'custom3' => null,
      'custom4' => null,
      'cargo' => match($item['cargo']) {
        'boxes' => 'Коробки',
        'pallets' => 'Палеты',
      },
      'pallets_count' => $item['pallets_count'],
      'pallets_boxcount' => $item['pallets_boxcount'],
      'pallets_weight' => $item['pallets_weight'],
      'pallets_volume' => $item['pallets_volume'],
      'custom5' => null,
      'boxes_count' => $item['boxes_count'],
      'custom6' => null,
      'fn1' => null,
      'fn2' => null,
      'fn3' => null,
      'custom7' => null,
      'boxes_volume' => strval(floatval($item['boxes_volume'])),
      'custom8' => null,
      'boxes_weight' => strval(floatval($item['boxes_weight'])),
      'palletizing_type' => empty($item['palletizing_type']) ? 'Нет' : 'Да',
      'palletizing_count' => $item['palletizing_count'],
      'transfer_method_pick' => match($this->transfer_method) {
        'receive' => 'Нет',
        'pick' => 'Да',
      },
      'receive_date' => $item['transfer_method_receive_date'],
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
    
    return [
      array_values($formatted),
    ];
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

  public function getPaymentMethodLabel($val): ?string
  {
    return match($val) {
      'cash' => 'Наличными при отправке',
      'bill' => 'По счету',
      default => null
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
