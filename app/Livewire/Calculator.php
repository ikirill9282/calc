<?php

namespace App\Livewire;

use App\Models\Distributor;
use App\Models\DistributorCenter;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Services\DadataClient;

class Calculator extends Component
{

    protected $listeners = [
    //   'fieldUpdated' => '$refresh',
      'runRefresh' => '$refresh',
      'setField',
    ];

    // public array $fields = [
    //   'warehouse_id' => 1,
    //   'distributor_id' => 2,
    //   'distributor_center_id' => 22,
    //   'delivery_date' => '31.05.2025',
    //   'transfer_method' => 'receive',
    //   'transfer_method.receive.date' => '30.05.2025',
    //   'transfer_method.pick.address' => null,
    //   'transfer_method.pick.date' => null,
    //   'transfer_method.pick.time' => null,
    // ];

    public array $fields = [
      'warehouse_id' => null,
      'distributor_id' => null,
      'distributor_center_id' => null,
      'delivery_date' => null,
      'transfer_method' => 'receive',
      'transfer_method.receive.date' => null,
      'transfer_method.pick.address' => null,
      'transfer_method.pick.date' => null,
      'transfer_method.pick.time' => null,
    ];

    protected array $times = [
      ['id' => '9:00-12:00', 'title' => 'c 9:00 до 12:00'],
      ['id' => '12:00-15:00', 'title' => 'c 12:00 до 15:00'],
      ['id' => '15:00-17:00', 'title' => 'c 15:00 до 17:00'],
    ];

    public array $addresses = [];

    // public function setWarehouse($value): void
    // {
    //   $this->warehouse = $value;
    // }

    public function mount()
    {
    }

    public function getWarehouses(): Collection
    {
      return Warehouse::all();
    }

    public function getDistributors(): Collection
    {
      return Distributor::all();
    }

    public function getDistributorCenters(): Collection
    {
      return DistributorCenter::query()
        ->when(
          !empty($this->fields['distributor_id']),
          fn($query) => $query->where('distributor_id', $this->fields['distributor_id'])
        )
        ->orderBy('title')
        ->get();
    }

    public function getAddresses()
    {
      $query = (empty($this->fields['transfer_method.pick.address'])) 
        ? 'г.Москва' 
        : $this->fields['transfer_method.pick.address'];

      $client = new DadataClient();
      $addresses = $client->suggest('address', $query);
      $this->addresses = array_column($addresses, 'value');
      $result = [['id' => '', 'title' => '']];
      foreach ($this->addresses as $key => $val) {
        $result[] = [
          'id' => $key,
          'title' => $val,
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
            if (empty($this->fields['transfer_method.receive.date'])) {
              return true;
            } else {
              return false;
            }
          } elseif ($this->fields['transfer_method'] == 'pick') {
            if (empty($this->fields['transfer_method.pick.address'])) {
              return true;
            } elseif (empty($this->fields['transfer_method.pick.date'])) {
              return true;
            } elseif (empty($this->fields['transfer_method.pick.time'])) {
              return true;
            } else {
              return false;
            }
          } else {
            return false;
          }
        }),
        default => true
      };
    }

    public function getField(string $name): mixed
    {
      return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
    }

    public function setField(string $name, mixed $value): void
    {
      
      if (in_array($name, ['transfer_method.pick.address'])) {
        $type = 'dropdown';
        $value = $this->addresses[$value];
      }
      
      $this->fields[$name] = $value;

      if ($name == 'distributor_id') {
        $this->clearField('distributor_center_id');
      }

      if (in_array($name, ['warehouse_id', 'distributor_id', 'distributor_center_id'])) {
        $className = str_ireplace('_id', '', $name);
        $className = implode("", array_map(fn($elem) => ucfirst($elem), explode('_', $className)));
        $className = '\App\Models\\'. $className;

        $value = $className::find($value)?->title ?? $value;
        $type = 'dropdown';
      }

      if (in_array($name, ['transfer_method.pick.time'])) {
        $type = 'dropdown';
        $value = collect($this->times)->where('id', $value)->first()['title'];
      }

      $this->dispatch('fieldUpdated', ['name' => $name, 'value' => $value, 'type' => $type ?? '']);
    }

    public function clearField(string $name): void
    {
      $this->fields[$name] = null;

      $type = 'datepicker';
      // if (in_array($name, ['warehouse_id', 'distributor_id', 'distributor_center_id'])) {
      if (in_array($name, ['warehouse_id', 'transfer_method.pick.address', 'transfer_method.pick.time'])) {
        $type = 'dropdown';
      }

      $this->dispatch('fieldClean', ['name' => $name, 'type' => $type]);
    }

    public function render()
    {
        return view('livewire.calculator');
    }
}
