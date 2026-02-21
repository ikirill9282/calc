<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Services\DadataClient;
use App\Support\SheetDataSchedule;
use App\Models\Agent;
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
use App\Services\GoogleClient;

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
        'weight' => null,
        'boxcount' => null,
        'volume' => null,
      ],
      'cargo_comment' => null,
      'cargo_type' => null,
      'palletizing_type' => null,
      'palletizing_count' => 0,
      'agent_id' => null,
      'payment_method' => null,
      'payment_method_pick' => null,
      'individual' => 0,
      'ozon_shipment_number' => null,
      'ozon_shipment_number_suffix' => null,
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

    public ?string $pick_address = null;
    protected string $lastAddressQuery = '';

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

            if (!empty($this->fields['ozon_shipment_number']) && str_starts_with((string) $this->fields['ozon_shipment_number'], '20000')) {
              $this->fields['ozon_shipment_number_suffix'] = substr($this->fields['ozon_shipment_number'], 5, 8);
            }
            Session::put('calc', json_encode($this->fields));
            $this->dispatch('runRefresh');
          }
        } catch (\Exception $e) {

        }
      } else if (Session::exists('calc')) {
        $this->fields = array_merge($this->fields, json_decode(Session::get('calc'), true));
        // Восстанавливаем суффикс номера поставки из полного номера (префикс 20000)
        if (!empty($this->fields['ozon_shipment_number']) && str_starts_with((string) $this->fields['ozon_shipment_number'], '20000')) {
          $this->fields['ozon_shipment_number_suffix'] = substr($this->fields['ozon_shipment_number'], 5, 8);
        }
      }

      $this->checkIndividual();
      $this->getAddresses();
      $this->onInitDatepickers();
    }

    public function updated($property, $value)
    {
      // Замена запятой на точку для полей объема и веса
      $decimalFields = [
        'fields.boxes_data.volume',
        'fields.boxes_data.weight',
        'fields.pallets_data.volume',
        'fields.pallets_data.weight',
      ];
      
      if (in_array($property, $decimalFields) && is_string($value)) {
        $normalizedValue = str_replace(',', '.', $value);
        if ($normalizedValue !== $value) {
          $key = str_ireplace('fields.', '', $property);
          Arr::set($this->fields, $key, $normalizedValue);
          return;
        }
      }

      // Номер поставки Ozon: только цифры, макс 8 после префикса 20000
      if ($property === 'fields.ozon_shipment_number_suffix') {
        $suffix = preg_replace('/\D/', '', (string) $value);
        $suffix = substr($suffix, 0, 8);
        $this->fields['ozon_shipment_number_suffix'] = $suffix;
        $this->fields['ozon_shipment_number'] = '20000' . $suffix;
        Session::put('calc', json_encode($this->fields));
      }

      if ($property == 'fields.transfer_method_pick.address') {
        $this->dropdownOpen[$property] = true;
        $this->getAddresses(Arr::get($this->fields, str_ireplace('fields.', '', $property)));
        $this->validatePickAddress();
      }
      // if ($property == 'pick_address') {
      //   $this->dropdownOpen[$property] = true;
      //   $this->getAddresses($this->pick_address);
      // }

      if ($property == 'fields.delivery_date' && $this->isValidCarbonDate($this->getField('delivery_date'))) {
        $this->fields['post_date'] = $this->getDeliveryDiff();
      }

      if ($property == 'fields.transfer_method') {
        if ($value === 'pick' && $this->getField('payment_method')) {
          $this->fields['payment_method_pick'] = $this->getField('payment_method');
        }

        if ($value !== 'pick') {
          $this->fields['payment_method_pick'] = null;
        }
      }

      if ($property == 'fields.payment_method' && $this->getField('transfer_method') === 'pick') {
        $this->fields['payment_method_pick'] = $value;
      }

      $this->checkIndividual();
      Session::put('calc', json_encode($this->fields));
    }

    function isValidCarbonDate(string $value): bool
    {
      $validator = Validator::make(
        ['value' => $value],
        ['value' => 'date'],
      );

      if ($validator->fails()) {
        return false;
      }

      return true;
    }

    function isHolidayPeriod(string $date): bool
    {
      $parsedDate = Carbon::parse($date);
      $holidayStart = Carbon::parse('2026-01-01');
      $holidayEnd = Carbon::parse('2026-01-04');
      
      return $parsedDate->gte($holidayStart) && $parsedDate->lte($holidayEnd);
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
     * If boxes density more than 300 - enable "individual" field.
     * If pallets weight more than 400 - enable "individual" field.
     */
    public function checkIndividual(): void
    {

      // For boxes
      if ($this->getField('cargo') == 'boxes') { 
        $volume = $this->getField('boxes_data.volume');
        $weight = $this->getField('boxes_data.weight');

        if (!empty($volume) && !empty($weight)) {
          // Преобразуем в числа для безопасного деления
          $volume = floatval($volume);
          $weight = floatval($weight);
          
          if ($volume > 0) {
          $density = round($weight / $volume);
          if ($density > 300) {
            $this->setField('individual', 1);
          } elseif ($this->getField('individual')) {
            $this->setField('individual', 0);
            }
          }
        }
      }

      // For pallets
      if ($this->getField('cargo') == 'pallets') {
        $pallets_weight = $this->getField('pallets_data.weight');
        
        if ($pallets_weight) {
          $pallets_weight = floatval($pallets_weight);
          if ($pallets_weight > 400) {
          $this->setField('individual', 1);
        } else {
          $this->setField('individual', 0);
          }
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

          if ($costs->isEmpty()) {
            return 0;
          }
          
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
          $deliveryDate = Carbon::parse($this->getField('delivery_date'))->format('Y-m-d');

          $routeQuery = $this->getRouteSheetDataQuery();

          $min = (clone $routeQuery)
            ->where('distributor_center_delivery_date', $deliveryDate)
            ->select('pick_tariff_min')
            ->first()
            ?->pick_tariff_min;

          if ($min === null) {
            $min = (clone $routeQuery)
              ->select('pick_tariff_min')
              ->orderByDesc('pick_tariff_min')
              ->first()
              ?->pick_tariff_min ?? 0;
          }

          $data = (clone $routeQuery)
            ->where('distributor_center_delivery_date', $deliveryDate)
            ->select('pick_tariff_vol', 'pick_tariff_pallete')
            ->groupBy(['pick_tariff_vol', 'pick_tariff_pallete'])
            ->get();

          if ($data->isEmpty()) {
            $data = (clone $routeQuery)
              ->select('pick_tariff_vol', 'pick_tariff_pallete')
              ->groupBy(['pick_tariff_vol', 'pick_tariff_pallete'])
              ->get();
          }

          if ($data->isEmpty()) {
            return 0;
          }

          $data = ($data->count() > 1)
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

    protected function getRouteSheetDataQuery()
    {
      return SheetData::query()
        ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->getField('warehouse_id'))
        ->where('distributor', $this->getField('distributor_id'))
        ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->getField('distributor_center_id'));
    }

    protected function getRouteSheetData(): Collection
    {
      if ($this->isFieldDisabled(2)) {
        return collect([]);
      }

      return $this->getRouteSheetDataQuery()->get();
    }

    protected function isWeekendAllowedForRoute(string $column, ?string $deliveryDate = null): bool
    {
      if ($this->isFieldDisabled(2)) {
        return true;
      }

      $query = $this->getRouteSheetDataQuery();

      if ($deliveryDate) {
        $forDate = (clone $query)
          ->where('distributor_center_delivery_date', $deliveryDate)
          ->select($column)
          ->orderByDesc('delivery_diff')
          ->first();

        if ($forDate && isset($forDate->{$column})) {
          return !intval($forDate->{$column});
        }
      }

      $fallback = (clone $query)
        ->select($column)
        ->orderByDesc('id')
        ->first();

      return !intval($fallback?->{$column} ?? 0);
    }

    public function getAddresses(string $query = '')
    {
      $query = $this->normalizeAddressQuery($query);

      if ($query === $this->lastAddressQuery && !empty($this->addresses)) {
        return collect($this->addresses);
      }

      $this->lastAddressQuery = $query;

      if (mb_strlen($query) < 3) {
        $this->addresses = [];
        return collect([]);
      }

      $client = new DadataClient();
      $searchQueries = $this->buildAddressSearchQueries($query);
      $suggestions = [];

      foreach ($searchQueries as $searchQuery) {
        // Базовый запрос без kwargs (как в рабочем tinker).
        $addresses = $client->suggest('address', $searchQuery, 12);

        if (is_array($addresses) && !empty($addresses)) {
          $suggestions = array_merge($suggestions, $addresses);
        }

        if (count($suggestions) >= 20) {
          break;
        }
      }

      // Дополнительно пробуем поиск домов по улице (избыточно полезно для "Батурина" -> д. 1, д. 2 ...).
      if (count($suggestions) < 8) {
        foreach ($searchQueries as $searchQuery) {
          $streetSearchQuery = $this->stripHouseFromAddressQuery($searchQuery);
          if (! $this->looksLikeStreetQuery($streetSearchQuery)) {
            continue;
          }

          $houseOptions = [
            'from_bound' => ['value' => 'street'],
            'to_bound' => ['value' => 'house'],
          ];

          $houseAddresses = $client->suggest('address', $streetSearchQuery, 12, $houseOptions);
          if (is_array($houseAddresses) && !empty($houseAddresses)) {
            $suggestions = array_merge($suggestions, $houseAddresses);
          }

          if (count($suggestions) >= 20) {
            break;
          }
        }
      }

      $resolved = [];
      foreach ($suggestions as $suggestion) {
        if (!is_array($suggestion)) {
          continue;
        }

        $addressValue = trim((string) ($suggestion['unrestricted_value'] ?? $suggestion['value'] ?? ''));
        if ($addressValue !== '') {
          $resolved[] = $addressValue;
        }
      }

      if (empty($resolved)) {
        $resolved = $this->resolveLocalAddressSuggestions($query);

        $streetOnly = $this->stripHouseFromAddressQuery($query);
        if (empty($resolved) && $streetOnly !== '' && $streetOnly !== $query) {
          $resolved = $this->resolveLocalAddressSuggestions($streetOnly);
        }
      }

      // Если внешние/локальные подсказки недоступны, оставляем введенное значение.
      if (empty($resolved)) {
        $region = $this->resolveAddressRegion();
        $city = $this->resolveAddressCity();
        $locationPrefix = trim(implode(', ', array_filter([$region, $city])));

        if ($locationPrefix !== '') {
          $resolved[] = "{$locationPrefix}, {$query}";
        }

        $resolved[] = $query;
      }

      $resolved = array_values(array_unique(array_slice(array_filter($resolved), 0, 20)));

      $result = [];
      foreach ($resolved as $key => $val) {
        $result[] = [
          'wh' => $val,
        ];
      }

      $this->addresses = $result;

      return collect($result);
    }

    protected function normalizeAddressQuery(string $query): string
    {
      $query = preg_replace('/\s+/u', ' ', $query) ?? $query;

      return trim($query, " \t\n\r\0\x0B,");
    }

    protected function stripHouseFromAddressQuery(string $query): string
    {
      $withoutHouse = preg_replace('/(?:,|\s)+(?:(?:д|дом|корп|к|стр|строение)\.?\s*)?\d+[а-яa-z0-9\-\/]*\s*$/ui', '', $query);

      if (!is_string($withoutHouse)) {
        return $query;
      }

      return $this->normalizeAddressQuery($withoutHouse);
    }

    protected function resolveAddressCity(): string
    {
      return trim(str_ireplace('г. ', '', $this->getCity()));
    }

    protected function resolveAddressRegion(): string
    {
      $warehouse = mb_strtolower((string) $this->getField('warehouse_id'));

      if (str_contains($warehouse, 'крым')) {
        return 'Респ Крым';
      }

      if (str_contains($warehouse, 'ростов')) {
        return 'Ростовская обл';
      }

      if (str_contains($warehouse, 'москва')) {
        return 'г Москва';
      }

      return '';
    }

    /**
     * @return array<int, string>
     */
    protected function buildAddressSearchQueries(string $query): array
    {
      $city = $this->resolveAddressCity();
      $region = $this->resolveAddressRegion();
      $streetOnly = $this->stripHouseFromAddressQuery($query);

      $queries = [$query];

      if ($streetOnly !== '' && $streetOnly !== $query) {
        $queries[] = $streetOnly;
      }

      if ($city !== '' && mb_stripos($query, $city) === false) {
        $queries[] = "{$city}, {$query}";

        if ($streetOnly !== '' && $streetOnly !== $query) {
          $queries[] = "{$city}, {$streetOnly}";
        }
      }

      if ($region !== '' && $city !== '') {
        $queries[] = "{$region}, {$city}, {$query}";

        if ($streetOnly !== '' && $streetOnly !== $query) {
          $queries[] = "{$region}, {$city}, {$streetOnly}";
        }
      }

      $result = [];
      foreach ($queries as $item) {
        $normalized = $this->normalizeAddressQuery($item);
        if ($normalized === '') {
          continue;
        }

        if (!in_array($normalized, $result, true)) {
          $result[] = $normalized;
        }
      }

      return $result;
    }

    protected function looksLikeStreetQuery(string $query): bool
    {
      return preg_match('/\p{L}/u', $query) === 1 && preg_match('/\d/u', $query) !== 1;
    }

    protected function resolveLocalAddressSuggestions(string $query): array
    {
      if (mb_strlen($query) < 4) {
        return [];
      }

      $userId = Auth::id();
      if (!$userId) {
        return [];
      }

      $fromAgents = Agent::query()
        ->where('user_id', $userId)
        ->whereNotNull('address')
        ->where('address', 'like', "%{$query}%")
        ->limit(7)
        ->pluck('address')
        ->toArray();

      $fromOrders = Order::query()
        ->where('user_id', $userId)
        ->whereNotNull('transfer_method_pick_address')
        ->where('transfer_method_pick_address', 'like', "%{$query}%")
        ->orderByDesc('id')
        ->limit(7)
        ->pluck('transfer_method_pick_address')
        ->toArray();

      return array_values(array_unique(array_merge($fromAgents, $fromOrders)));
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
          } elseif (empty($this->fields['delivery_date']) || !$this->isValidCarbonDate($this->getField('delivery_date'))) {
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
            if (empty($this->getField('transfer_method_receive.date')) || !$this->isValidCarbonDate($this->getField('transfer_method_receive.date'))) {
              return true;
            } else {
              return false;
            }
          } elseif ($this->fields['transfer_method'] == 'pick') {
            if (empty($this->getField('transfer_method_pick.address'))) {
              return true;
            } elseif (!$this->validatePickAddress()) {
              return true;
            } elseif (empty($this->getField('transfer_method_pick.date')) || !$this->isValidCarbonDate(empty($this->getField('transfer_method_pick.date')))) {
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

    public function openDropdown(string $name): void
    {
      $this->dropdownOpen = [];
      $this->dropdownOpen[$name] = true;

      if ($name === 'fields.transfer_method_pick.address') {
        $value = (string) Arr::get($this->fields, 'transfer_method_pick.address', '');
        if (mb_strlen($this->normalizeAddressQuery($value)) >= 3) {
          $this->getAddresses($value);
        }
      }
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
      $key = str_ireplace('fields.', '', $name);
      Arr::set($this->fields, $key, $value);

      unset($this->dropdownOpen["fields.$key"]);

      $this->clearRelated($key);
      $this->refreshDatepickersForField($key);

      if ($key == 'transfer_method_pick.address') {
        $this->getAddresses(Arr::get($this->fields, 'transfer_method_pick.address'));
        $this->validatePickAddress();
        unset($this->dropdownOpen["fields.$key"]);
      }

      // if ($key == 'pick_address') {
      //   $this->pick_address = $value;
      //   $this->getAddresses($value);
      //   $this->setField('transfer_method_pick.address', $value);
      //   unset($this->dropdownOpen["pick_address"]);
      // }

      if ($key == 'delivery_date') {
        $this->fields['post_date'] = $this->getDeliveryDiff();
      }

      if ($key == 'palletizing_count') {
        if ($value == 0) $this->fields['palletizing_type'] = null;
      }

      Session::put('calc', json_encode($this->fields));
    }

    protected function refreshDatepickersForField(string $key): void
    {
      $routeKeys = ['warehouse_id', 'distributor_id', 'distributor_center_id'];

      // Тяжелый расчет deliveryDates нужен только при смене маршрута.
      if (in_array($key, $routeKeys, true) && ! $this->isFieldDisabled(2)) {
        $this->dispatch('deliveryDates', $this->getDeliveryDates());
      }

      // Даты отгрузки/забора обновляем при смене маршрута или даты поставки.
      if ((in_array($key, $routeKeys, true) || $key === 'delivery_date') && ! $this->isFieldDisabled(3)) {
        $this->dispatch('deliveryPickDates', $this->getDeliveryPickDates());
        $this->dispatch('pickDates', $this->getPickDates());
      }
    }

    public function clearField(string $name) {
      $key = str_ireplace('fields.', '', $name);
      $value = null;

      // if (in_array($key, $this->numeric_fields)) {
      //   $value = 0;
      // }

      Arr::set($this->fields, $key, $value);
      if ($key === 'ozon_shipment_number') {
        $this->fields['ozon_shipment_number_suffix'] = null;
      }
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
        $deliveryDate = $date ?? Carbon::parse($this->getField('delivery_date'))->format('Y-m-d');

        $routeQuery = $this->getRouteSheetDataQuery();

        $exactDate = (clone $routeQuery)
          ->where('distributor_center_delivery_date', $deliveryDate)
          ->select('delivery_diff')
          ->orderByDesc('delivery_diff')
          ->first()
          ?->delivery_diff;

        if ($exactDate) {
          return Carbon::parse($exactDate)->format('Y-m-d H:i:s');
        }

        $recordsColumns = [
          'distributor_center_delivery_date',
          'delivery_diff',
          'pick_diff',
          'transit_days',
        ];

        if (SheetData::hasWeekdayConfigColumns()) {
          $recordsColumns[] = 'delivery_weekdays_config';
          $recordsColumns[] = 'shipment_weekdays_config';
        }

        $records = (clone $routeQuery)->get($recordsColumns);
        if ($records->isEmpty()) {
          return null;
        }

        $transitDays = SheetDataSchedule::transitDays($records);
        $shipmentWeekdays = SheetDataSchedule::shipmentWeekdays($records);
        $shipmentDate = SheetDataSchedule::resolveShipmentDate(Carbon::parse($deliveryDate), $shipmentWeekdays, $transitDays);

        return $shipmentDate?->format('Y-m-d H:i:s');

      }

      return null;
    }

    public function getDeliveryDates(): array
    {

      if (!$this->isFieldDisabled(2)) {
        $records = $this->getRouteSheetData();
        if ($records->isEmpty()) {
          return [];
        }

        $deliveryWeekdays = SheetDataSchedule::deliveryWeekdays($records);
        if (empty($deliveryWeekdays)) {
          return [];
        }

        $allowWeekend = $this->isWeekendAllowedForRoute('delivery_weekend');

        $today = Carbon::today();
        $maxConfiguredDate = $records->max('distributor_center_delivery_date');
        $rangeEnd = $maxConfiguredDate
          ? Carbon::parse($maxConfiguredDate)->startOfDay()
          : $today->copy();

        // Всегда показываем минимум 90 дней вперед по конфигурации маршрута.
        $minHorizon = $today->copy()->addDays(90);
        if ($rangeEnd->lt($minHorizon)) {
          $rangeEnd = $minHorizon;
        }

        $result = [];
        $current = $today->copy();

        while ($current->lte($rangeEnd)) {
          $isConfiguredWeekday = in_array($current->dayOfWeek, $deliveryWeekdays, true);

          if (! $isConfiguredWeekday) {
            $current->addDay();
            continue;
          }

          if (! $allowWeekend && $current->isWeekend()) {
            $current->addDay();
            continue;
          }

          $date = $current->format('Y-m-d');

          if ($this->isHolidayPeriod($date)) {
            $current->addDay();
            continue;
          }

          if ($this->getDeliveryDiff($date) !== null) {
            $result[] = $date;
          }

          $current->addDay();
        }

        return $result;
      }
      return [];
    }

    public function getDeliveryPickDates(?string $delivery_date = null): array
    {
      if (!$this->isFieldDisabled(3) || $delivery_date) {
        $delivery_date = $delivery_date ?? Carbon::parse($this->getField('delivery_date'))->format('Y-m-d');

        $date = $this->getDeliveryDiff($delivery_date);
        if (empty($date)) {
          return [];
        }

        $point_date = Carbon::parse($date);
        if ($point_date->lt(Carbon::today())) {
          return [];
        }

        $weekend = $this->isWeekendAllowedForRoute('delivery_weekend', $delivery_date);

        
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

        $result = array_filter($result, function($date) {
          if (
            $date == Carbon::today()->format('Y-m-d')
            && Carbon::now()->gte(Carbon::today()->setHours(16))
          ) {
            return false;
          }

          // Исключаем праздничные даты (01.01-04.01.2026)
          if ($this->isHolidayPeriod($date)) {
            return false;
          }

          return Carbon::parse($date)->gte(Carbon::today());
        });

        return array_values($result);
      }
      return [];
    }

    public function getPickDates(): array
    {
      if (!$this->isFieldDisabled(3)) {
        $deliveryDate = Carbon::parse($this->getField('delivery_date'))->format('Y-m-d');

        $date = $this->getRouteSheetDataQuery()
          ->where('distributor_center_delivery_date', $deliveryDate)
          ->select('pick_diff')
          ->orderByDesc('pick_diff')
          ->first()
        ;

        $pointDateRaw = $date?->pick_diff ?? $this->getDeliveryDiff($deliveryDate);
        if (empty($pointDateRaw)) {
          return [];
        }

        $point_date = Carbon::parse($pointDateRaw);
        if ($point_date->lt(Carbon::today())) {
          return [];
        }

        $weekend = $this->isWeekendAllowedForRoute('pick_weekend', $deliveryDate);

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
        $result = array_filter($result, function($date) {
          if (
            $date == Carbon::today()->format('Y-m-d')
            && Carbon::now()->gte(Carbon::today()->setHours(15))
          ) {
            return false;
          }
          
          // Исключаем праздничные даты (01.01-04.01.2026)
          if ($this->isHolidayPeriod($date)) {
            return false;
          }
          
          return Carbon::parse($date)->gte(Carbon::today());
        });

        return array_values($result);
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
          'ozon_shipment_number' => 'nullable|string|regex:/^20000\d{8}$/',
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
          'ozon_shipment_number.required' => 'Необходимо заполнить поле',
          'ozon_shipment_number.regex' => 'Номер поставки: в начале 20000, затем 8 цифр (всего 13 цифр).',
          'palletizing_count' => 'Введите целое число',
        ]
      );
      $validator->sometimes('ozon_shipment_number', 'required|string|regex:/^20000\d{8}$/', function ($input) {
        $distributor = $input['distributor_id'] ?? '';
        return stripos($distributor, 'Ozon') !== false || stripos($distributor, 'ОЗОН') !== false;
      });
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      return true;
    }

    public function validatePickAddress()
    {
      // Костыль до выяснения
      if (preg_match('/[0-9]+/is', $this->getField('transfer_method_pick.address'))) {
        return true;
      } else {
        $this->addError('transfer_method_pick.address', 'Необходимо заполнить город, улицу и дом');
        return false;
      }

      $client = new DadataClient();
      $result = $client->clean('address', $this->getField('transfer_method_pick.address'));
      
      $city = $result['city'] ?? null;
      $street = $result['street'] ?? null;
      $house = $result['house'] ?? null;
      $qc_house = $result['qc_house'] ?? 10;
      
      if ($city && $street && $house && in_array($qc_house, [2, 3])) {
        return true;
      } else {
        $this->addError('transfer_method_pick.address', 'Необходимо заполнить город, улицу и дом');
        return false;   
      }
    }
    
    public function prepareOrder()
    {
      $fields = $this->fields;
      unset($fields['user_focused_dropdown'], $fields['user_address_query'], $fields['ozon_shipment_number_suffix']);

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
          // try {
          $order->save();
          // } catch (\Exception $e) {
            
          // }

          // Отправляем данные в Google Sheets сразу после создания заявки
          // чтобы синхронизировать send_date из колонки H
          try {
            $data = $order->prepareSheetData();
            GoogleClient::write($data[0]);
            $order->print()->firstOrCreate();
          } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем процесс создания заявки
            \Log::error("Failed to send order to Google Sheets immediately after creation", [
              'order_id' => $order->id,
              'error' => $e->getMessage(),
            ]);
          }

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

    public function getPaymentMethodTitle(): string
    {
      return $this->getField('transfer_method') === 'pick'
        ? 'Способ оплаты доставки и забора груза'
        : 'Способ оплаты доставки груза';
    }
}
