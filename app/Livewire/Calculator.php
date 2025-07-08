<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use App\Models\Distributor;
use App\Models\DistributorCenter;
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

    // public array $fields = [
    //     'agent_id' => null,
    //     'warehouse_id' => 'Ростов-на-Дону Ростовская область, г Ростов-на-Дону, пр. 40-летия Победы, 85/4А1',
    //     'distributor_id' => 'Wildberries',
    //     'distributor_center_id' => 'Подольск 2 (WB)',
    //     'delivery_date' => '21.06.2025',
    //     'transfer_method' => 'pick',
    //     'transfer_method_receive' => [
    //       'date' => '19.06.2025',
    //     ],
    //     'transfer_method_pick' => [
    //       'address' => 'г Москва, г Щербинка ',
    //       'date' => '19.06.2025',
    //     ],
    //     'user_address_query' => null,
    //     'user_focused_dropdown' => null,
    //     'boxes' => true,
    //     'boxes_data' => [
    //       'count' => 2,
    //       'volume' => 1.2,
    //       'weight' => 1234,
    //     ],
    //     'pallets' => true,
    //     'pallets_data' => [
    //       'count' => 2,
    //       'volume' => 4,
    //     ],
    //     'cargo_comment' => null,
    //     'cargo_type' => null,
    //     'palletizing_type' => null,
    //     'palletizing_count' => 0,
    // ];

    public array $fields = [
      'warehouse_id' => null,
      'distributor_id' => null,
      'distributor_center_id' => null,
      'delivery_date' => null,
      'post_date' => null,
      'transfer_method' => null,
      'transfer_method_receive' => [
         'date' => null,
      ],
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
      ],
      'pallets_data' => [
        'count' => null,
        'weight' => null,
      ],
      'cargo_comment' => null,
      'cargo_type' => null,
      'palletizing_type' => null,
      'palletizing_count' => 0,
      'agent_id' => null,
      'payment_method' => null,
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

    // public function setWarehouse($value): void
    // {
    //   $this->warehouse = $value;
    // }

    public array $dropdownOpen = [];

    public function mount()
    {
      // Session::forget('calc');

      if (request()->has('reply')) {
        Session::forget('calc');
        try {
          $id = Crypt::decrypt(request()->get('reply'));
          $order = Order::find($id);
          $clear_fields = ['delivery_date', 'transfer_method_receive_date', 'transfer_method_pick_date'];
          if ($order) {
            foreach ($order->toArray() as $key => $val) {
              if ($key == 'post_date') continue;
              
              // if (in_array($key, ['boxes', 'pallets'])) {
              //   $this->fields[$key] = boolval($val);
              //   continue;
              // }

              if ($key == 'transfer_method_pick_address') {
                $this->fields['transfer_method_pick']['address'] = $val;
                continue;
              }

              if (in_array($key, ['boxes_count', 'boxes_volume', 'boxes_weight', 'pallets_count', 'pallets_volume'])) {
                $parts = explode('_', $key);
                $this->fields["{$parts[0]}_data"][$parts[1]] = $val;
                continue;
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

      $this->getAddresses();
      $this->onInitDatepickers();
    }

    public function updated($property)
    {
      if ($property == 'fields.transfer_method_pick.address') {
        $this->dropdownOpen[$property] = true;
        $this->getAddresses(Arr::get($this->fields, str_ireplace('fields.', '', $property)));
      }

      if ($property == 'fields.delivery_date') {
        $this->fields['post_date'] = $this->getDeliveryDiff();
      }

      Session::put('calc', json_encode($this->fields));
    }

    #[On('initDatepickers')]
    public function onInitDatepickers()
    {
      if (!$this->isFieldDisabled(2)) {
        $this->dispatch('deliveryDates', $this->getDeliveryDates());
      }

      // dd($this->isFieldDisabled(3));
      if (!$this->isFieldDisabled(3)) {
        $this->dispatch('deliveryPickDates', $this->getDeliveryPickDates());
        $this->dispatch('pickDates', $this->getPickDates());
      }
    }

    public function getAmount(): int
    {
      $pick_amount = match($this->getField('transfer_method')) {
        'receive' => 0,
        'pick' => $this->getPickAmount(),
        default => 0,
      };
      $additional = $this->getAdditionalAmount();
      $delivery = $this->getDeliveryAmount();

      // dump($this->getField('transfer_method'));
      // dump($pick_amount, $additional, $delivery);
      // dump($pick_amount + $additional + $delivery);
      return ceil($pick_amount + $additional + $delivery);
    }

    public function getAdditionalAmount(): int
    {
      if (!$this->canCalcBoxes() && !$this->canCalcPallets()) {
        return 0;
      }
      $quant = match($this->getField('palletizing_type')) {
        'single' => 250,
        'pallet' => 650,
        default => 0,
      };

      return $quant ? ($this->getField('palletizing_count') * $quant) : 0;
    }

    public function getDeliveryAmount(): int
    {
      $result = 0;

      if (!$this->isFieldDisabled(4)) {
          $costs = SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
            ->where('distributor', $this->getField('distributor_id'))
            ->where('distributor_center', $this->getField('distributor_center_id'))

            /** DISABLE FOR TEST */
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
            $cost_vol = $vol * $costs['delivery_tariff_vol'];
            
            $builded_pallets = ($this->getField('palletizing_type') == 'pallet') 
              ? $this->getField('palletizing_count')
              : 0;

            $cost_builded_pallets = $builded_pallets * $costs['delivery_tariff_pallete'];

            $result += max($costs['delivery_tariff_min'], $cost_vol, $cost_builded_pallets);
          }
          
          if ($this->canCalcPallets()) {
            // $cost_vol = $this->getField('pallets_data.volume') * $costs['delivery_tariff_vol'];
            $cost_pallet = $this->getField('pallets_data.count') * $costs['delivery_tariff_pallete'];
            // $result += max($cost_vol, $cost_pallet);
            return $cost_pallet;
          }
      }

      return ceil($result);
    }

    public function getPickAmount(): int
    {
      $result = 0;

      if (!$this->isFieldDisabled(4)) {
          $min = SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
            ->where('distributor', $this->getField('distributor_id'))
            ->where('distributor_center', $this->getField('distributor_center_id'))
            
            /** DISABLE FOR TEST */
            // ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
            
            ->select('pick_tariff_min')
            ->first()
            ?->pick_tariff_min ?? 0
          ;

          $data = SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
            ->where('distributor', $this->getField('distributor_id'))
            ->where('distributor_center', $this->getField('distributor_center_id'))

            /** DISABLE FOR TEST */
            // ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
            
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
            $cost_vol = $vol * $data['pick_tariff_vol'];

            $builded_pallets = ($this->getField('palletizing_type') == 'pallet') 
              ? $this->getField('palletizing_count')
              : 0;

            $cost_builded_pallets = $builded_pallets * $data['pick_tariff_pallete'];

            $result += max($min, $cost_vol, $cost_builded_pallets);
          }
          
          if ($this->canCalcPallets()) {

            $cost_vol = $this->getField('pallets_data.volume') * $data['pick_tariff_vol'];
            $cost_pallet = $this->getField('pallets_data.count') * $data['pick_tariff_pallete'];
            $result += max($cost_vol, $cost_pallet);

            // $pallets = $this->getField('pallets_data.count');
            // $result += ($pallets * $data['pick_tariff_pallete']);
          }
      }

      return ceil($result);
    }

    public function canCalcBoxes(): bool
    {
      return $this->getField('cargo') == 'boxes'
            && !empty($this->getField('boxes_data.count')) 
            && !empty($this->getField('boxes_data.volume'))
            ;
    }

    public function canCalcPallets(): bool
    {
      return $this->getField('cargo') == 'pallets'
            && !empty($this->getField('pallets_data.count'))
            && !empty($this->getField('pallets_data.weight'))
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
          ->select('distributor_center')
          ->distinct()
          // ->ddRawSql()
          ->get()
          // ->map(function($item) {
          //   $arr = $item->toArray();
          //   $arr['wh'] = $item['distributor_center'];
          //   return $arr;
          // });
          ;
      }
      return collect([]);
    }

    public function getAddresses(string $query = '')
    {
      $query = empty($query) ? 'г Москва' : $query;
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
            // dd('ok');
          } else {
            if ($this->fields['cargo'] == 'pallets') {
              $pallets_data = $this->getField('pallets_data');
              foreach ($pallets_data as $key => $val) {
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
      // if (str_contains($name, '.')) {
      //   return Arr::get($this->fields, $name);
      // }
      // return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
      $key = str_ireplace('fields.', '', $name);
      $val = Arr::get($this->fields, $key);
      if (in_array($key, $this->numeric_fields)) {
        $val = floatval($val);
      }

      return $val;
    }
 
    public function setField(string $name, mixed $value)
    {
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

      Session::put('calc', json_encode($this->fields));
    }

    public function clearField(string $name) {
      $key = str_ireplace('fields.', '', $name);
      $value = null;

      if (in_array($key, $this->numeric_fields)) {
        $value = 0;
      }

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

    public function getWarehousePhone(): ?string
    {
      return empty($this->fields['warehouse_id']) ? null : Warehouse::find($this->fields['warehouse_id'])?->phone;
    }

    public function getDeliveryDiff(): ?string
    {
      if (!$this->isFieldDisabled(3)) {
        /** FOR TESTING */
        return Carbon::parse($this->getField('delivery_date'))->modify('-2 days')->format('Y-m-d H:i:s');

        return SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where('distributor_center', $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
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
        

        /** FOR TESTING */
        $today = Carbon::today();
        $end = Carbon::today()->modify('+1 week');
        $result = [];
        while($today->lte($end)) {
          array_push($result, $today->format('Y-m-d'));
          $today->modify('+1 day');
        }

        return $result;

        $data = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where('distributor_center', $this->getField('distributor_center_id'))
          ->select('distributor_center_delivery_date')
          // ->ddRawSql()
          ->get()
          ->pluck('distributor_center_delivery_date')
        ;

        // dd($data->toArray());
        
        $weekend = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where('distributor_center', $this->getField('distributor_center_id'))
          // ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('delivery_weekend')
          ->groupBy('delivery_weekend')
          // ->ddRawSql()
          ->get()
          ;
        $weekend = count($weekend) > 1 ? 1 : $weekend[0] ?? 1;

        $result = $data->toArray();
        $result = $weekend ? $result : array_values(array_filter($result, fn($date) => !Carbon::parse($date)->isWeekend()));

        return array_values(array_filter($result, fn($date) => Carbon::parse($date)->gte(Carbon::today())));
        // return $result;
      }
      return [];
    }

    public function getDeliveryPickDates(): array
    {
      if (!$this->isFieldDisabled(3)) {


        /** FOR TESTING */
        $today = Carbon::today();
        $end = Carbon::today()->modify('+2 week');
        $result = [];
        while($today->lte($end)) {
          array_push($result, $today->format('Y-m-d'));
          $today->modify('+1 day');
        }

        return $result;


        $date = $this->getDeliveryDiff();
        $point_date = Carbon::parse($date);

        $weekend = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where('distributor_center', $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('delivery_weekend')
          ->orderByDesc('delivery_diff')
          ->first()
          ;
        $weekend = $weekend?->delivery_weekend;

        $diff = Carbon::today()->diffInDays($point_date);
        $result = [];

        for ($i = 0; $i < $diff; $i++) {
          $date = Carbon::today()->modify("+$i days");
          if ($date->isWeekend() && !$weekend) continue;

          array_push($result, $date->format('Y-m-d'));
        }
        array_push($result, $point_date->format('Y-m-d'));
        sort($result, SORT_DESC);

        return array_filter($result, fn($date) => Carbon::parse($date)->gte(Carbon::today()));
        // return $result;
      }
      return [];
    }

    public function getPickDates(): array
    {
      if (!$this->isFieldDisabled(3)) {

        /** FOR TESTING */
        $today = Carbon::today();
        $end = Carbon::today()->modify('+3 week');
        $result = [];
        while($today->lte($end)) {
          array_push($result, $today->format('Y-m-d'));
          $today->modify('+1 day');
        }

        return $result;

        $date = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where('distributor_center', $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('pick_diff')
          ->first()
        ;

        $point_date = Carbon::parse($date?->pick_diff);

        $weekend = SheetData::query()
          ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
          ->where('distributor', $this->getField('distributor_id'))
          ->where('distributor_center', $this->getField('distributor_center_id'))
          ->where('distributor_center_delivery_date', Carbon::parse($this->getField('delivery_date'))->format('Y-m-d'))
          ->select('pick_weekend')
          ->first()
          ;
        $weekend = $weekend?->delivery_weekend;

        $diff = Carbon::today()->diffInDays($point_date);
        $result = [];

        for ($i = 0; $i < $diff; $i++) {
          $date = Carbon::today()->modify("+$i days");
          if ($date->isWeekend() && !$weekend) continue;

          array_push($result, $date->format('Y-m-d'));
        }
        array_push($result, $point_date->format('Y-m-d'));
        sort($result, SORT_DESC);

        // dd(Carbon::today());
        return array_filter($result, fn($date) => Carbon::parse($date)->gte(Carbon::today()));
        // return $result;
      }
      return [];
    }

    public function clearRelated(string $name)
    {
      if ($name == 'warehouse_id') {
        
        $this->fields['distributor_id'] = null;
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
          'pallets_data.count' => 'required_if:cargo,pallets|nullable|integer',
          'pallets_data.weight' => 'required_if:cargo,pallets|nullable|numeric',
          "cargo_comment" => 'sometimes|nullable|string',
          "cargo_type" => 'sometimes|nullable|string',
          "palletizing_type" => 'sometimes|string',
          "palletizing_count" => 'sometimes|integer',
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
        dd($validator->errors(), $this->fields);
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
      $order->pick = $this->getPickAmount();
      $order->delivery = $this->getDeliveryAmount();
      $order->additional = $this->getAdditionalAmount();
      $order->total = $this->getAmount();

      return $order;
    }

    public function submit()
    {
      // dd($this->fields);s
      if ($this->validateFields()) {
        if ($this->checkout) {
          $validator = Validator::make($this->fields, [
            'agent_id' => 'required|integer',
            'payment_method' => 'required|string',
          ]);
          if ($validator->fails()) {
            throw new ValidationException($validator);
          }
          $order = $this->prepareOrder();
          // dd($order);
          $order->save();

          Session::forget('calc');
          return redirect('/success/?order='.Crypt::encrypt($order->id));
        } else {
          $this->checkout = true;
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
