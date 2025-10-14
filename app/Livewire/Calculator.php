<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Services\DadataClient;
use App\Models\Manager;
use App\Models\SheetData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class Calculator extends Component
{

    protected $listeners = [
      'runRefresh' => '$refresh',
      'setField',
    ];

    public array $fields = [
      'warehouse_id' => null,
      'distributor_id' => 'Wildberries',
      'distributor_center_id' => null,
      'delivery_date' => null,
      'post_date' => null,
      'transfer_method' => null,

      'transfer_method_receive' => [
        'date' => null,
      ],
      'transfer_method_pick' => [
        'address' => null,
        'date' => null,
      ],
      'cargo' => null,
      'boxes_data' => [
        'count' => null,
        'volume' => null,
        'weight' => null,
      ],
      'pallets_data' => [
        'count' => null,
      ],
      'cargo_comment' => null,
      'cargo_type' => null,
      'palletizing_type' => null,
      'palletizing_count' => 0,
      'agent_id' => null,
      'payment_method' => null,
      'payment_method_pick' => null,
      'individual' => 0,
    ];

    protected array $numeric_fields = [
      'boxes_data.count', 
      'boxes_data.volume', 
      'pallets_data.count', 
      'pallets_data.volume',
    ];

    protected array $times = [
      ['id' => '9:00-12:00', 'title' => 'c 9:00 до 12:00'],
      ['id' => '12:00-15:00', 'title' => 'c 12:00 до 15:00'],
      ['id' => '15:00-17:00', 'title' => 'c 15:00 до 17:00'],
    ];

    public array $addresses = [];

    public bool $checkout = false;

    public array $dropdownOpen = [];

    public function mount()
    {
      // Session::forget('calc');

      $this->checkout = Session::exists('checkout') ? Session::get('checkout') : $this->checkout;

      if (request()->has('reply')) {
        Session::forget('calc');
        try {
          $id = Crypt::decrypt(request()->get('reply'));
          $order = Order::find($id);
          $clear_fields = ['delivery_date', 'transfer_method_receive_date', 'transfer_method_pick_date'];
          if ($order) {
            foreach ($order->toArray() as $key => $val) {
              if ($key == 'post_date') continue;
              
              if ($key == 'transfer_method_pick_address') {
                $this->fields['transfer_method_pick']['address'] = $val;
                continue;
              }

              if (in_array($key, ['boxes_count', 'boxes_volume', 'boxes_weight', 'pallets_count', 'pallets_weight'])) {
                continue;
                $parts = explode('_', $key);
                $this->fields["{$parts[0]}_data"][$parts[1]] = $val;
              }

              if (array_key_exists($key, $this->fields) && !in_array($key, $clear_fields)) {
                $this->fields[$key] = $val;
              }
            }

            Session::put('calc', json_encode($this->fields));
            $this->dispatch('runRefresh');
          }
        } catch (\Exception $e) {

        }
      } else if (Session::exists('calc')) {
        $this->fields = array_merge($this->fields, json_decode(Session::get('calc'), true));
      }

      $this->checkIndividual();
      $this->getAddresses();
      $this->onInitDatepickers();
    }
    
    public function updating($property, $value)
    {
      if (str_ends_with($property, 'date')) {
        try {
          Carbon::parse($value);
        } catch (\Exception $e) {
          throw ValidationException::withMessages([
            "$property" => ['Неверный формат даты'],
          ]);
          dd('ok');
        }
      }
    }

    public function updated($property, $value)
    {
      if ($property == 'fields.transfer_method_pick.address') {
        $this->dropdownOpen[$property] = true;
        $this->getAddresses(Arr::get($this->fields, str_ireplace('fields.', '', $property)));
      }

      if ($property == 'fields.delivery_date') {
        $this->fields['post_date'] = $this->getDeliveryDiff();
      }

      $this->checkIndividual();
      Session::put('calc', json_encode($this->fields));
    }

    function isValidCarbonDate(string $dateString): bool
    {
      try {
        Carbon::parse($dateString);
        return true;
      } catch (\Exception $e) {
        return false;
      }
    }

    #[On('initDatepickers')]
    public function onInitDatepickers()
    {
      if (!$this->isFieldDisabled(2)) {
        $this->dispatch('deliveryDates', $this->getDeliveryDates());
      }

      if (!$this->isFieldDisabled(3)) {
        $this->dispatch('deliveryPickDates', $this->getDeliveryPickDates());
        $this->dispatch('pickDates', $this->getPickDates());
      }
    }

    /**
     * If density more than 300 - enable "individual" field.
     */
    public function checkIndividual(): void
    {
      // Only for boxes
      if ($this->getField('cargo') == 'pallets') {
        $this->setField('individual', 0);
        return ;
      }

      $volume = $this->getField('boxes_data.volume');
      $weight = $this->getField('boxes_data.weight');

      if (!empty($volume) && !empty($weight)) {
        $density = round($weight / $volume);
        if ($density > 300) {
          $this->setField('individual', 1);
        } elseif ($this->getField('individual')) {
          $this->setField('individual', 0);
        }
      }
    }

    /**
     * Get total price.
     * @return integer
     */
    public function getAmount(): int
    {
      $pick_amount = match($this->getField('transfer_method')) {
        'receive' => 0,
        'pick' => $this->getPickAmount(),
        default => 0,
      };

      // dump($this->getField('individual'));
      $additional = $this->getAdditionalAmount();
      $delivery = $this->getDeliveryAmount();

      // dump($this->getField('transfer_method'));
      // dump($pick_amount, $additional, $delivery);
      // dump($pick_amount + $additional + $delivery);
      return ceil($pick_amount + $additional + $delivery);
    }

    public function getAdditionalAmount(): int
    {

      if ($this->getField('individual')) return 0;

      if ($this->getField('cargo') == 'boxes') {
        return 0;
      }

      if (!$this->canCalcBoxes() && !$this->canCalcPallets()) {
        return 0;
      }

      $quant = match($this->getField('palletizing_type')) {
        'single' => 800,
        'pallet' => 800,
        default => 0,
      };

      return $quant ? ($this->getField('palletizing_count') * $quant) : 0;
    }

    public function getDeliveryAmount(): int
    {
      $result = 0;

      if ($this->getField('individual')) return $result;

      if (!$this->isFieldDisabled(4)) {
          $costs = SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
            ->where('distributor', $this->getField('distributor_id'))
            ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))

            // ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
            
            ->select(['delivery_tariff_min', 'delivery_tariff_vol', 'delivery_tariff_pallete'])
            ->groupBy(['delivery_tariff_min', 'delivery_tariff_vol', 'delivery_tariff_pallete'])
            ->get()
            ;
          
          $costs = ($costs?->count() > 1) 
            ? [
                'delivery_tariff_min' => $costs->max('delivery_tariff_min'),
                'delivery_tariff_vol' => $costs->max('delivery_tariff_vol'),
                'delivery_tariff_pallete' => $costs->max('delivery_tariff_pallete'),
              ]
            : $costs->first()->toArray();
          
          
          if ($this->canCalcBoxes()) {
            $vol = $this->getField('boxes_data.volume');
            $vol = ceil($vol / 0.05) * 0.05;
            $cost_vol = $vol * $costs['delivery_tariff_vol'];

            // $builded_pallets = ($this->getField('palletizing_type') == 'pallet') 
            //   ? $this->getField('palletizing_count')
            //   : 0;

            // $cost_builded_pallets = $builded_pallets * $costs['delivery_tariff_pallete'];

            // $result += max($costs['delivery_tariff_min'], $cost_vol, $cost_builded_pallets);
            $result += max($costs['delivery_tariff_min'], $cost_vol);
          }
          
          if ($this->canCalcPallets()) {
            // $cost_vol = $this->getField('pallets_data.volume') * $costs['delivery_tariff_vol'];
            $cost_pallet = $this->getField('pallets_data.count') * $costs['delivery_tariff_pallete'];
            // $result += max($cost_vol, $cost_pallet);
            return ceil($cost_pallet);
          }
      }

      return $result;
    }

    public function getPickAmount(): int
    {
      $result = 0;
      if ($this->getField('individual')) return $result;

      if (!$this->isFieldDisabled(4)) {
          $min = SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
            ->where('distributor', $this->getField('distributor_id'))
            ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
            
            /** DISABLE FOR TEST */
            ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
            
            ->select('pick_tariff_min')
            ->first()
            ?->pick_tariff_min ?? 0
          ;

          $data = SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
            ->where('distributor', $this->getField('distributor_id'))
            ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))

            /** DISABLE FOR TEST */
            ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
            
            ->select('pick_tariff_vol', 'pick_tariff_pallete')
            ->groupBy(['pick_tariff_vol', 'pick_tariff_pallete'])
            ->get();
          ;

          // dd($data);

          $data = ($data?->count() > 1) 
          ? [
              'pick_tariff_vol' => $data->max('pick_tariff_vol'),
              'pick_tariff_pallete' => $data->max('pick_tariff_pallete'),
            ]
          : $data->first()->toArray();

          

          if ($this->canCalcBoxes()) {
            $vol = $this->getField('boxes_data.volume');
            $vol = ceil($vol / 0.05) * 0.05;
            $cost_vol = $vol * $data['pick_tariff_vol'];

            // $builded_pallets = ($this->getField('palletizing_type') == 'pallet') 
            //   ? $this->getField('palletizing_count')
            //   : 0;

            // $cost_builded_pallets = $builded_pallets * $data['pick_tariff_pallete'];

            // $result += max($min, $cost_vol, $cost_builded_pallets);

            $result += max($min, $cost_vol);
          }
          
          if ($this->canCalcPallets()) {
            
            // $cost_vol = $this->getField('pallets_data.volume') * $data['pick_tariff_vol'];
            $cost_pallet = $this->getField('pallets_data.count') * $data['pick_tariff_pallete'];
            // $result += max($cost_vol, $cost_pallet);
            // dd($cost_pallet);
            return ceil($cost_pallet);
            // $pallets = $this->getField('pallets_data.count');
            // $result += ($pallets * $data['pick_tariff_pallete']);
          }
      }

      return $result;
    }

    public function canCalcBoxes(): bool
    {
      return $this->getField('cargo') == 'boxes'
            && !empty($this->getField('boxes_data.count')) 
            && !empty($this->getField('boxes_data.volume'))
            // && !empty($this->getField('boxes_data.weight'))
            ;
    }

    public function canCalcPallets(): bool
    {
      return $this->getField('cargo') == 'pallets'
            && !empty($this->getField('pallets_data.count'))
            // && !empty($this->getField('pallets_data.weight'))
            ;
    }

    public function getWarehouses(): Collection
    {
      return SheetData::select('wh', 'wh_address')->groupBy(['wh','wh_address'])->get();
    }

    public function getDistributors(): Collection
    {
      return SheetData::select('distributor')->distinct()->get();
    }

    public function getDistributorCenters(): Collection
    {
      if (!empty($this->getField('warehouse_id')) && !empty($this->getField('distributor_id'))) {
        return SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->select(
            'distributor_center', 
            'distributor_address',
            DB::raw('CONCAT(distributor_center, " ", distributor_address) as val')
          )
          ->distinct()
          // ->ddRawSql()
          ->get()
          // ->dd()
          // ->map(function($item) {
          //   $arr = $item->toArray();
          //   $arr['val'] = $item['distributor_center'] . ' ' . $item['distributor_address'];
          //   return $arr;
          // });
          ;
      }
      return collect([]);
    }

    public function getAddresses(string $query = '')
    {
      $query = empty($query) ? '' : $query;
      $client = new DadataClient();
      $addresses = $client->suggest('address', $query);
      $this->addresses = array_column($addresses, 'value');
      // $result = [['wh' => '', 'wh_address' => '']];
      $result = [];
      foreach ($this->addresses as $key => $val) {
        $result[] = [
          'wh' => $val,
          // 'wh_address' => $val,
        ];
      }
      return collect($result);
    }

    public function isFieldDisabled(int $field_number): bool
    {
      return match($field_number) {
        1 => false,
        2 => call_user_func(function() {
          if (empty($this->fields['distributor_center_id'])) {
            return true;
          } elseif (empty($this->fields['distributor_id'])) {
            return true;
          } elseif (empty($this->fields['warehouse_id'])) {
            return true;
          } else {
            return false;
          }
        }),
        3 => call_user_func(function() {
          if ($this->isFieldDisabled(2)) {
            return true;
          } elseif (empty($this->fields['delivery_date'])) {
            return true;
          } else {
            return false;
          }
        }),
        4 => call_user_func(function() {
          if ($this->isFieldDisabled(3)) {
            return true;
          } elseif (empty($this->fields['transfer_method'])) {
            return true;
          } elseif ($this->fields['transfer_method'] == 'receive') {
            if (empty($this->getField('transfer_method_receive.date'))) {
              return true;
            } else {
              return false;
            }
          } elseif ($this->fields['transfer_method'] == 'pick') {
            if (empty($this->getField('transfer_method_pick.address'))) {
              return true;
            } elseif (empty($this->getField('transfer_method_pick.date'))) {
              return true;
            } else {
              return false;
            }
          } else {
            return false;
          }
        }),
        5 => call_user_func(function() {
          if ($this->isFieldDisabled(4)) {
            return true;
          }

          if (empty($this->fields['cargo'])) {
            return true;
          }

          if ($this->fields['cargo'] == 'boxes') {
            $boxes_data = $this->getField('boxes_data');
            foreach ($boxes_data as $key => $val) {
              if (empty($val)) return true;
            }
          } else {
            if ($this->fields['cargo'] == 'pallets') {
              $pallets_data = $this->getField('pallets_data');
              foreach ($pallets_data as $key => $val) {
                // if ($key == 'weight') continue;
                if (empty($val)) return true;
              }
            }
          }

          return false;
        }),
        // 6 => call_user_func(function() {
        //   if ($this->isFieldDisabled(5)) return true;
          
        //   if (empty($this->fields['cargo_type'])) return true;

        //   return false;
        // }),
        7 => call_user_func(function() {
          if ($this->isFieldDisabled(5)) return true;

          // foreach ($this->fields['delivery_type'] as $item) {
          //   if ($item) {
          //     return false;
          //   }
          // }

          return false;
        }),
        default => true
      };
    }

    public function clearFocusedAndSetField(string $name, mixed $value):void
    {
      
      $this->fields['user_focused_dropdown'] = null;
      $this->setField($name, $value);
      // $this->fields[$name] = $value;
    }

    public function getField(string $name): mixed
    {
      $key = str_ireplace('fields.', '', $name);
      $val = Arr::get($this->fields, $key);
      if (in_array($key, $this->numeric_fields)) {
        $val = floatval($val);
      }

      return $val;
    }
 
    public function setField(string $name, mixed $value)
    {

      if (str_ends_with($name, 'date')) {
        try {
          Carbon::parse($value);
        } catch (\Exception $e) {
          $this->addError('fields.delivery_date', 'Неверный формат даты'); 
          return ;
        }
      }

      $key = str_ireplace('fields.', '', $name);
      Arr::set($this->fields, $key, $value);

      $this->clearRelated($key);
      $this->onInitDatepickers();

      if ($key == 'transfer_method_pick.address') {
        $this->getAddresses(Arr::get($this->fields, 'transfer_method_pick.address'));
        unset($this->dropdownOpen["fields.$key"]);
      }

      if ($key == 'delivery_date') {
        $this->fields['post_date'] = $this->getDeliveryDiff();
      }

      if ($key == 'palletizing_count') {
        if ($value == 0) $this->fields['palletizing_type'] = null;
      }

      Session::put('calc', json_encode($this->fields));
    }

    public function clearField(string $name) {
      $key = str_ireplace('fields.', '', $name);
      $value = null;

      // if (in_array($key, $this->numeric_fields)) {
      //   $value = 0;
      // }

      Arr::set($this->fields, $key, $value);
      $this->clearRelated($key);
      Session::put('calc', json_encode($this->fields));
    }

    public function showManager()
    {
      $distributor_center_id = $this->fields['distributor_center_id'] ?? null;
      $manager = null;

      if ($distributor_center_id) {
        $manager = Manager::whereHas('distributorCenters', function ($query) use ($distributor_center_id) {
          $query->where('id', $distributor_center_id);
        })->first();
        $this->dispatch('openManagerModal', $manager);
      } else {
        $this->dispatch('openManagerModal');
      }

    }

    public function getWarehouseAddress(): ?string
    {
      return empty($this->fields['warehouse_id']) 
        ? null 
        : SheetData::where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))->first()?->wh_address ?? $this->getField('warehouse_id');
    }

    public function getCity(): string
    {
      if (str_contains(mb_strtolower($this->getField('warehouse_id')), 'симферополь')) {
        return 'г. Симферополь';
      } else if (str_contains(mb_strtolower($this->getField('warehouse_id')), 'ростов-на-дону')) {
        return 'г. Ростов-на-дону';
      } else if (str_contains(mb_strtolower($this->getField('warehouse_id')), 'москва')) {
        return 'г. Москва';
      }

      return '';
    }

    public function getWarehousePhone(): ?string
    {
      return empty($this->fields['warehouse_id']) ? null : Warehouse::find($this->fields['warehouse_id'])?->phone;
    }

    public function getDeliveryDiff(?string $date = null): ?string
    {
      if (!$this->isFieldDisabled(2)) {
        /** FOR TESTING */
        // return Carbon::parse($this->getField('delivery_date'))->modify('-2 days')->format('Y-m-d H:i:s');
        return SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', $date ?? Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('delivery_diff')
          ->orderByDesc('delivery_diff')
          ->first()
          ?->delivery_diff
        ;

      }

      return null;
    }

    public function getDeliveryDates(): array
    {

      if (!$this->isFieldDisabled(2)) {

        $data = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
          ->select('distributor_center_delivery_date')
          // ->ddRawSql()
          ->get()
          ->pluck('distributor_center_delivery_date')
        ;
        
        $weekend = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
          // ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('delivery_weekend')
          ->groupBy('delivery_weekend')
          // ->ddRawSql()
          ->first()
          ;
        
        
        $weekend = !intval($weekend?->delivery_weekend);
        
        
        $result = $data->toArray();
        $result = $weekend ? $result : array_values(array_filter($result, fn($date) => !Carbon::parse($date)->isWeekend()));

        $result = array_values(array_filter($result, fn($date) => Carbon::parse($date)->gte(Carbon::today())));

        foreach($result as $k => $date) {
          $sub_dates = $this->getDeliveryPickDates($date);
          if (empty($sub_dates)) unset($result[$k]);
        }
        return array_values($result);
      }
      return [];
    }

    public function getDeliveryPickDates(?string $delivery_date = null): array
    {
      if (!$this->isFieldDisabled(3) || $delivery_date) {
        $delivery_date = $delivery_date ?? Carbon::parse($this->getField('delivery_date'))->format('Y-m-d');

        $date = $this->getDeliveryDiff($delivery_date);
        
        $point_date = Carbon::parse($date);

        $weekend = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', $delivery_date)
          ->select('delivery_weekend')
          ->orderByDesc('delivery_diff')
          ->first()
          ;

        $weekend = !intval($weekend?->delivery_weekend);

        
        $diff = Carbon::today()->diffInDays($point_date);
        $result = [];

        for ($i = 0; $i < $diff; $i++) {
          $date = Carbon::today()->modify("+$i days");
          if ($date->isWeekend() && !$weekend) continue;

          array_push($result, $date->format('Y-m-d'));
        }

        if ($point_date->isWeekend() && intval($weekend)) {
          array_push($result, $point_date->format('Y-m-d'));
        } elseif (!$point_date->isWeekend()) {
          array_push($result, $point_date->format('Y-m-d'));
        }
        
        sort($result, SORT_DESC);

        return array_filter($result, fn($date) => Carbon::parse($date)->gte(Carbon::today()));
      }
      return [];
    }

    public function getPickDates(): array
    {
      if (!$this->isFieldDisabled(3)) {

        $date = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('pick_diff')
          ->first()
        ;

        $point_date = Carbon::parse($date?->pick_diff);

        $weekend = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('pick_weekend')
          ->first()
          ;

        $weekend = !intval($weekend?->pick_weekend);

        $diff = Carbon::today()->diffInDays($point_date);
        $result = [];

        for ($i = 0; $i < $diff; $i++) {
          $date = Carbon::today()->modify("+$i days");
          if ($date->isWeekend() && !$weekend) continue;

          array_push($result, $date->format('Y-m-d'));
        }
        
        if ($point_date->isWeekend() && intval($weekend)) {
          array_push($result, $point_date->format('Y-m-d'));
        } elseif (!$point_date->isWeekend()) {
          array_push($result, $point_date->format('Y-m-d'));
        }
        sort($result, SORT_DESC);

        return array_filter($result, fn($date) => Carbon::parse($date)->gte(Carbon::today()));
      }
      return [];
    }

    public function getPostDate(): ?string
    {
      return match($this->fields['transfer_method']) {
        'receive' => $this->getField('transfer_method_receive.date'),
        'pick' => $this->getField('transfer_method_pick.date'),
        default => null,
      };
    }

    public function clearRelated(string $name)
    {
      if ($name == 'warehouse_id') {
        
        // $this->fields['distributor_id'] = null;
        $this->fields['distributor_center_id'] = null;
        $this->fields['delivery_date'] = null;
        $this->fields['transfer_method_pick']['date'] = null;
        $this->fields['transfer_method_receive']['date'] = null;
        $this->fields['palletizing_type'] = null;
        $this->fields['palletizing_count'] = null;
      }
      
      if ($name == 'distributor_id') {
        
        $this->fields['distributor_center_id'] = null;
        $this->fields['delivery_date'] = null;
        $this->fields['transfer_method_pick']['date'] = null;
        $this->fields['transfer_method_receive']['date'] = null;
        $this->fields['palletizing_type'] = null;
        $this->fields['palletizing_count'] = null;
      }
      
      if ($name == 'distributor_center_id') {
        
        $this->fields['delivery_date'] = null;
        $this->fields['transfer_method_pick']['date'] = null;
        $this->fields['transfer_method_receive']['date'] = null;
        $this->fields['palletizing_type'] = null;
        $this->fields['palletizing_count'] = null;
      }
      
      if ($name == 'delivery_date') {
        
        $this->fields['transfer_method_pick']['date'] = null;
        $this->fields['transfer_method_receive']['date'] = null;
      }

      if ($name == "pallets_data.count") {
        $this->fields['palletizing_type'] = null;
        $this->fields['palletizing_count'] = 0;
      }
    }

    public function validateFields(): bool
    {
        $validator = Validator::make($this->fields, [
          "warehouse_id" => "required|string",
          "distributor_id" => "required|string",
          "distributor_center_id" => "required|string",
          "delivery_date" => "required|string",
          "post_date" => "required|string",
          "transfer_method" => "required|string",
          "transfer_method_receive.date" => 'required_if:transfer_method,=,receive|nullable|string',
          "transfer_method_pick.address" => "required_if:transfer_method,=,pick|nullable|string",
          "transfer_method_pick.date" => "required_if:transfer_method,=,pick|nullable|string",
          'cargo' => 'string|required',
          'boxes_data.count' => 'required_if:cargo,boxes|nullable|integer',
          'boxes_data.volume' => 'required_if:cargo,boxes|nullable|numeric',
          'boxes_data.weight' => 'required_if:cargo,boxes|nullable|numeric',
          'pallets_data.count' => 'required_if:cargo,pallets|nullable|integer',
          // 'pallets_data.weight' => 'required_if:cargo,pallets|nullable|numeric',
          "cargo_comment" => 'sometimes|nullable|string',
          "cargo_type" => 'sometimes|nullable|string',
          "palletizing_type" => 'sometimes|nullable|string',
          "palletizing_count" => 'sometimes|nullable|integer',
          // 'agent_id' => 'required|integer',
          // 'payment_method' => 'required|string',
          // 'payment_method_pick' => 'required|string',
        ],
        [
          'boxes_data.count.required_if' => 'Необходимо заоплнить поле',
          'boxes_data.volume.required_if' => 'Необходимо заоплнить поле',
          'pallets_data.count.required_if' => 'Необходимо заоплнить поле',
          'pallets_data.volume.required_if' => 'Необходимо заоплнить поле',
          'transfer_method_receive.date.required_if' => 'Необходимо заоплнить поле',
          'transfer_method_pick.address.required_if' => 'Необходимо заоплнить поле',
          'transfer_method_pick.date.required_if' => 'Необходимо заоплнить поле',
          'palletizing_count' => 'Введите целое число',
        ]
      );
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      return true;
    }

    public function prepareOrder()
    {
      $fields = $this->fields;
      unset($fields['user_focused_dropdown'], $fields['user_address_query']);

      $order = new Order();
      $order->fillFields($fields);
      $order->user_id = Auth::user()?->id;
      $order->pick = ($this->fields['transfer_method'] == 'pick') ? $this->getPickAmount() : 0;
      $order->delivery = $this->getDeliveryAmount();
      $order->additional = $this->getAdditionalAmount();
      $order->total = $this->getAmount();

      return $order;
    }

    public function back(): void
    {
      $this->checkout = false;
      Session::put('checkout', $this->checkout);
    }

    public function submit()
    {
      if ($this->validateFields()) {
        if ($this->checkout) {
          // dd($this->fields);
          $validator = Validator::make($this->fields, [
            'agent_id' => 'required|integer',
            'payment_method_pick' => 'required_if:transfer_method,pick|nullable|string',
            'payment_method' => 'required|string',
          ], [
            'agent_id' => 'Выберите контрагента',
            'payment_method' => 'Выберите способ оплаты',
            'payment_method_pick' => 'Выберите способ оплаты',
          ]);
          if ($validator->fails()) {
            throw new ValidationException($validator);
          }
          $order = $this->prepareOrder();
          $order->save();

          Session::forget('calc');
          return redirect('/success/?order='.Crypt::encrypt($order->id));
        } else {
          $this->checkout = true;
          Session::put('checkout', $this->checkout);
        }
      }

    }

    public function goToAgents()
    {
      return redirect('/agents');
    }

    public function render()
    {
      return view('livewire.calculator');
    }
}
