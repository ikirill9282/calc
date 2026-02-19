<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\DadataClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use App\Models\Order;

class Agents extends Component
{
    public $form = [
      'title' => null,
      'inn' => null,
      'ogrn' => null,
      'address' => null,
      'name' => null,
      'phone' => null,
      'email' => null,

      // "title" => "Test",
      // "inn" => "123123132123",
      // "ogrn" => "123123123123",
      // "address" => "г Москва, мкр Северное Чертаново",
      // "name" => "Test",
      // "phone" => "+7(123)123-12-31",
      // "email" => "test@test.com",
    ];

    public $agents_open = [];

    public $listeners = [
      'refresh' => '$refresh',
    ];

    public $agents;

    public $edit_mode = false;

    public $edit_model = null;

    public $messages = [];

    public $dropdownOpen = [];

    public $addresses = [];

    public $companies = [];
    public $company = [];

    public function mount()
    {
      $this->getAddresses();
      $this->reloadAgents();
    }

    public function updated($property)
    {
      if ($property == 'form.address') {
        $this->dropdownOpen[$property] = true;
        $this->getAddresses(Arr::get($this->form, str_ireplace('form.', '', $property)));
      }
      if ($property == 'form.inn') {
        $inn = preg_replace('/\D+/', '', (string) $this->form['inn']);
        $this->form['inn'] = $inn;

        if (strlen($inn) >= 10) {
          $this->dropdownOpen[$property] = true;
          $this->getCompanies($inn);
        }
      }
    }

    #[On('clearField')]
    
    public function clearField(string $name): void
    {
      $key = str_ireplace('form.', '', $name);
      Arr::set($this->form, $key, null);

      if ($name == 'form.address') {
        $this->getAddresses();
      }

      if ($name == 'form.inn' || $name == 'inn') {
        $this->form['title'] = null;
        $this->form['ogrn'] = null;
        $this->form['address'] = null;
        $this->company = [];
        $this->companies = [];
      }
    }

    #[On('clearMessages')]
    public function clearMessages(): void
    {
      $this->messages = [];
    }

    public function reloadAgents(): void
    {
      $this->agents = Agent::where('user_id', Auth::user()->id)->where('disabled', 0)->get();
    }

    public function getAddresses(?string $query = 'г Москва')
    {
      $query = empty($this->form['address']) ? $query : $this->form['address'];
      $query = $query ?: 'г Москва';
      $client = new DadataClient();
      $addresses = $client->suggest('address', $query);
      $addresses = array_column($addresses, 'value');

      $result = [];
      foreach ($addresses as $key => $val) {
        $result[] = [
          'wh' => $val,
        ];
      }
      
      $this->addresses = collect($result);
    }

    public function setAddress(string $val): void
    {
      $this->form['address'] = $val;
    }

    public function submit()
    {
      $validator = Validator::make(
        $this->form, 
        [
          'title' => 'required|string',
          'inn' => 'required|string',
          'ogrn' => 'required|string',
          'address' => 'nullable|string',
          'name' => 'required|string',
          'phone' => 'required|string',
          'email' => 'required|string|email:dns',
        ],
        [
          'title.required' => 'Необходимо заполнить поле',
          'inn.required' => 'Необходимо заполнить поле',
          'ogrn.required' => 'Необходимо заполнить поле',
          'name.required' => 'Необходимо заполнить поле',
          'phone.required' => 'Необходимо заполнить поле',
          'email.required' => 'Необходимо заполнить поле',

          'title.string' => 'Поле должно быть текстом',
          'inn.integer' => 'Поле должно быть числом',
          'ogrn.integer' => 'Поле должно быть числом',
          'address.string' => 'Поле должно быть текстом',
          'name.string' => 'Поле должно быть текстом',
          'phone.string' => 'Поле должно быть текстом',
          'email.string' => 'Поле должно быть текстом',

          'email.string' => 'Поле должно быть Email адресом',
        ]
      );

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $valid = $validator->validated();
      $valid['user_id'] = Auth::user()->id;
      
      // Преобразуем пустой или null address в пустую строку вместо null
      if (!isset($valid['address']) || $valid['address'] === null || trim($valid['address']) === '') {
        $valid['address'] = '';
      }
      
      $agent = Agent::where([
        'user_id' => $valid['user_id'],
        'title' => $valid['title'],
        'ogrn' => $valid['ogrn'],
        'inn' => $valid['inn'],
      ])->first();

      if (
        !$this->edit_mode &&
        $agent
      ) {
        $agent->update([
          'disabled' => 0,
          'name' => $valid['name'],
          'phone' => $valid['phone'],
          'email' => $valid['email'],
          'address' => $valid['address'] ?? '',
        ]);
        $this->reloadAgents();
        return ;
      }

      if ($this->edit_mode && $this->edit_model) {
        Agent::where('id', $this->edit_model)->update($valid);
        $this->messages[] = 'Контрагент обновлен';
      } else {
        Agent::create($valid);
        $this->messages[] = 'Контрагент добавлен';
      }

      $this->refreshForm();      
      $this->reloadAgents();
      $this->dispatch('refresh');
      $this->agents_open = [$this->edit_model];
    }

    public function edit(int $id)
    {
      $agent = Agent::find($id);
      if (!$agent) {
        $this->setErrorBag(['general' => 'Контрагент не найден']);
        return;
      }
      $this->agents_open[] = $id;
      $this->edit_mode = true;
      $this->edit_model = $id;
      $this->refreshForm([
        'title' => $agent->title,
        'inn' => $agent->inn,
        'ogrn' => $agent->ogrn,
        'address' => $agent->address,
        'name' => $agent->name,
        'phone' => $agent->phone,
        'email' => $agent->email,
      ]);
      // $this->dispatch('refresh');
    }

    public function cancelEdit()
    {
      $this->edit_mode = false;
      $this->edit_model = null;
      $this->refreshForm();
    }

    public function delete(int $agent_id)
    {
      Agent::where('id', $agent_id)->update(['disabled' => 1]);
      $this->reloadAgents();
    }

    public function refreshForm(array $data = [])
    {
      $this->form = array_merge(
        [
          'title' => null,
          'inn' => null,
          'ogrn' => null,
          'address' => null,
          'name' => null,
          'phone' => null,
          'email' => null,
        ],
        $data
      );
    }


    public function getCompanies(string $query = '')
    {
      $query = $query;
      $client = new DadataClient();
      $data = $client->suggest('party', $query);
      // dd($data);
      // unset($data[0]['data']['address']);
      // dd($data[0]['data']['address']['unrestricted_value'] ?? $data[0]['data']['address']['value'] ?? '', $data[0]);
      $result = [];
      foreach ($data as $key => $item) {
        $formatted = [
          'name' => $item['value'],
          'inn' => $item['data']['inn'] ?? '',
          'ogrn' => $item['data']['ogrn'] ?? '',
          'address' => $item['data']['address']['unrestricted_value'] ?? $item['data']['address']['value'] ?? null,
          'manager' => $item['data']['management']['name'] ?? null,
          'phone' => $item['data']['phones'][0] ?? null,
          'email' => $item['data']['emails'][0] ?? null,
        ];
        $formatted['key'] = md5(serialize($formatted));
        $formatted['description'] = 'ИНН: ' . $formatted['inn'] . ', ОГРН: ' . $formatted['ogrn'];
        $result[] = $formatted;
      }
      $this->companies = $result;
      // dd($this->companies);
      return collect($result);
    }


    public function getField(string $name): mixed
    {
      return Arr::get($this->form, str_ireplace('form.', '', $name));
    }

    public function openDropdown(string $name): void
    {
      $this->dropdownOpen = [];
      $this->dropdownOpen[$name] = true;
    }


    public function setField(string $name, mixed $value): void
    {
      Arr::set($this->form, str_ireplace('form.', '', $name), $value);
      
      if ($name == 'form.inn') {
        $company = collect($this->companies)->where('key', $value)->first();
        if ($company) {
          $this->form['title'] = $company['name'];
          $this->form['inn'] = $company['inn'];
          $this->form['ogrn'] = $company['ogrn'];
          $this->form['address'] = $company['address'];
          $this->company = $company;
          $this->getAddresses($company['address'] ?? null);
          unset($this->dropdownOpen[$name]);
          unset($this->dropdownOpen['form.address']);
        }
      }
      
      if ($name == 'form.address') {
        $this->getAddresses();
        unset($this->dropdownOpen[$name]);
      }
    }

    public function render()
    {
        return view('livewire.agents');
    }
}
