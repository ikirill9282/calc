<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Revolution\Google\Sheets\Facades\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use App\Models\SheetData;
use App\Services\OrderCostCalculator;

class Order extends Model
{
  public const FIELD_LABELS = [
    'id' => '№ заявки',
    'created_at' => 'Дата создания',
    'updated_at' => 'Дата изменения',
    'user_id' => 'Пользователь',
    'agent_id' => 'Отправитель',
    'delivery_date' => 'Дата поставки на РЦ',
    'send_date' => 'Дата отправки',
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
    'driver_name' => 'ФИО водителя',
  ];

  protected $casts = [
    'changed_fields' => 'array',
    'highlight_color' => 'string',
    'send_date' => 'date',
  ];

  /**
   * @var array<string>
   */
  protected array $dateAttributes = [
    'send_date',
  ];

  /**
   * @var array<string>
   */
  protected array $dateTimeAttributes = [
    'delivery_date',
    'post_date',
    'transfer_method_receive_date',
    'transfer_method_pick_date',
  ];

  /**
   * @var array<string>
   */
  protected static array $pricingRecalculationFields = [
    'warehouse_id',
    'distributor_id',
    'distributor_center_id',
    'delivery_date',
    'transfer_method',
    'transfer_method_receive_date',
    'transfer_method_pick_date',
    'transfer_method_pick_address',
    'payment_method',
    'payment_method_pick',
    'cargo',
    'boxes_count',
    'boxes_volume',
    'boxes_weight',
    'pallets_count',
    'pallets_boxcount',
    'pallets_volume',
    'pallets_weight',
    'palletizing_type',
    'palletizing_count',
    'individual',
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
      } else {
        $hasManualCostChanges = $model->isDirty('pick')
          || $model->isDirty('delivery')
          || $model->isDirty('additional');

        if ($model->shouldRecalculatePricing()) {
          // Сохраняем вручную измененные поля стоимости
          $manualPick = $model->isDirty('pick') ? $model->pick : null;
          $manualDelivery = $model->isDirty('delivery') ? $model->delivery : null;
          $manualAdditional = $model->isDirty('additional') ? $model->additional : null;

          $model->recalculatePricing();

          // Восстанавливаем вручную измененные значения
          if ($manualPick !== null) {
            $model->pick = $manualPick;
          }
          if ($manualDelivery !== null) {
            $model->delivery = $manualDelivery;
          }
          if ($manualAdditional !== null) {
            $model->additional = $manualAdditional;
          }
        }

        // Всегда пересчитываем total как сумму pick + delivery + additional
        // Это гарантирует, что total будет правильным при создании и обновлении заявки
        // Выполняем это в самом конце, после всех манипуляций с полями стоимости
        $expectedTotal = $model->cash_expected_total;
        if ($expectedTotal !== null) {
          $model->total = (int) ceil($expectedTotal);
        } elseif ($model->pick === null && $model->delivery === null && $model->additional === null) {
          // Если все компоненты null, то и total должен быть null
          $model->total = null;
        } else {
          // Если хотя бы один компонент установлен, пересчитываем total
          $sum = ($model->pick ?? 0) + ($model->delivery ?? 0) + ($model->additional ?? 0);
          $model->total = $sum > 0 ? (int) ceil($sum) : null;
        }
      }

      if (!$model->exists) {
        return;
      }

      // Сохраняем оригинальные значения дат в начале, до любых изменений
      $dateFields = ['delivery_date', 'transfer_method_receive_date', 'transfer_method_pick_date'];
      $originalDates = [];
      $dirtyFieldsBefore = array_keys($model->getDirty());
      
      foreach ($dateFields as $dateField) {
        $originalDates[$dateField] = $model->getRawOriginal($dateField);
      }

      $dirty = array_keys($model->getDirty());
      $ignore = ['created_at', 'updated_at', 'changed_fields'];
      $changed = array_values(array_diff($dirty, $ignore));
      
      // Восстанавливаем оригинальные значения дат, если они не были изменены явно
      // и исключаем их из списка измененных полей
      foreach ($originalDates as $dateField => $originalValue) {
        // Проверяем, было ли поле изменено ДО того, как мы начали сохранять
        if (!in_array($dateField, $dirtyFieldsBefore, true)) {
          // Если поле не было изменено явно, восстанавливаем оригинальное значение
          $currentValue = $model->getAttribute($dateField);
          // Сравниваем значения, чтобы не перезаписывать, если они одинаковые
          if ($currentValue != $originalValue) {
            $model->setAttribute($dateField, $originalValue);
            $model->syncOriginalAttribute($dateField);
          }
          // Исключаем поле даты из списка измененных полей
          $changed = array_values(array_diff($changed, [$dateField]));
        }
      }
      
      // После восстановления значений дат, обновляем список dirty полей
      // чтобы убедиться, что даты не попали в список измененных
      $dirty = array_keys($model->getDirty());
      $changed = array_values(array_diff($dirty, array_merge($ignore, $dateFields)));

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

  protected static array $distributorCenterNameCache = [];

  public function distributionLabel(): string
  {
    $parts = array_filter([
      $this->distributor_id,
      $this->resolveDistributorCenterName() ?? $this->distributor_center_id,
    ], fn ($value) => filled($value));

    return implode(' - ', $parts);
  }

  protected function resolveDistributorCenterName(): ?string
  {
    if (blank($this->distributor_center_id) || blank($this->distributor_id)) {
      return null;
    }

    $cacheKey = $this->distributor_id . '|' . $this->distributor_center_id;

    if (array_key_exists($cacheKey, static::$distributorCenterNameCache)) {
      return static::$distributorCenterNameCache[$cacheKey];
    }

    $centerName = SheetData::query()
      ->where('distributor', $this->distributor_id)
      ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->distributor_center_id)
      ->value('distributor_center');

    return static::$distributorCenterNameCache[$cacheKey] = $centerName;
  }

  public function getDistributionLabelAttribute(): string
  {
    return $this->distributionLabel();
  }

  public function getCashExpectedTotalAttribute(): ?float
  {
    $components = [$this->pick, $this->delivery, $this->additional];
    $sum = 0.0;
    $hasValue = false;

    foreach ($components as $value) {
      if ($value === null) {
        continue;
      }

      $sum += (float) $value;
      $hasValue = true;
    }

    return $hasValue ? $sum : null;
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


  public function getCity()
  {
    return SheetData::query()
      ->where(DB::raw("CONCAT(wh, ' ', wh_address)"), '=', $this->warehouse_id)
      ->select('wh')
      ->distinct()
      ->first()
      ?->wh ?? '';
      
  }

  public function getWarehouseAddress(): ?string
  {
    if (empty($this->warehouse_id)) {
      return null;
    }

    return SheetData::where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->warehouse_id)
      ->first()
      ?->wh_address ?? $this->warehouse_id;
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
    // Заменяем warehouse_id на адрес склада для отправки в Google таблицу
    if (!empty($order_data['warehouse_id'])) {
      $order_data['warehouse_id'] = $this->getWarehouseAddress() ?? $order_data['warehouse_id'];
    }
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
      'warehouse_address' => $this->getWarehouseAddress() ?? '',
      'empty_at' => '', // Уникальный ключ
      'empty_au' => '', // Отсеивание дубликатов
      'empty_av' => '', // Чистый объем без дублей
      'empty_aw' => '', // Чистое кол-во паллет без дублей
      'empty_ax' => '', // Сумма объема по клиенту
      'empty_ay' => '', // Сумма кол-ва паллет по клиенту
      'empty_az' => '', // Формула расчета забора
      'empty_ba' => '', // Ручное изменение стоимости забора
      'ozon_shipment_number' => $item['ozon_shipment_number'] ?? '',
    ];

    $formatted = array_map(fn($val) => is_null($val) ? '' : $val, $formatted);
    
    return [
      array_values($formatted),
    ];
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

      try {
        $this->setAttribute($field, $value);
      } catch (\Exception $e) {
        //
      } catch (\Error $e) {
        //
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

  public function shouldRecalculatePricing(): bool
  {
    if ($this->individual) {
      return false;
    }

    if (! $this->exists) {
      return true;
    }

    return $this->isDirty(static::$pricingRecalculationFields);
  }

  public function recalculatePricing(): void
  {
    // Для calcM используем упрощенную логику - просто пересчитываем total из существующих значений
    // Если нужен полный пересчет через OrderCostCalculator, его нужно будет добавить
    $expectedTotal = $this->cash_expected_total;
    $this->total = $expectedTotal !== null ? (int) ceil($expectedTotal) : null;
  }
}
