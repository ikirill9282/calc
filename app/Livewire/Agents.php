<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\DadataClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;

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

    public function mount()
    {
      $this->reloadAgents();
    }

    #[On('clearField')]
    public function clearField(string $name): void
    {
      if (array_key_exists($name, $this->form)) {
        $this->form[$name] = null;
      }
    }

    #[On('clearMessages')]
    public function clearMessages(): void
    {
      $this->messages = [];
    }

    public function reloadAgents(): void
    {
      $this->agents = Agent::where('user_id', Auth::user()->id)->get();
    }

    public function getAddresses()
    {
      $query = empty($this->fields['user_address_query']) ? 'г Москва' : $this->fields['user_address_query'];
      $client = new DadataClient();
      $addresses = $client->suggest('address', $query);
      $addresses = array_column($addresses, 'value');

      $result = [];
      foreach ($addresses as $key => $val) {
        $result[] = [
          'wh' => $val,
        ];
      }

      return collect($result);
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
          'inn' => 'required|integer',
          'ogrn' => 'required|integer',
          'address' => 'required|string',
          'name' => 'required|string',
          'phone' => 'required|string',
          'email' => 'required|string',
        ],
        [
          'title.required' => 'Необходимо заполнить поле',
          'inn.required' => 'Необходимо заполнить поле',
          'ogrn.required' => 'Необходимо заполнить поле',
          'address.required' => 'Необходимо заполнить поле',
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
        ]
      );

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $valid = $validator->validated();
      $valid['user_id'] = Auth::user()->id;
      
      if (
        !$this->edit_mode &&
        Agent::where([
          'user_id' => $valid['user_id'],
          'title' => $valid['title'],
          'ogrn' => $valid['ogrn'],
          'inn' => $valid['inn'],
        ])
        ->exists()
      ) {
        $this->setErrorBag([
          'title' => 'Контрагент уже существует',
          'inn' => 'Контрагент уже существует',
          'ogrn' => 'Контрагент уже существует',
        ]);
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

    public function render()
    {
        return view('livewire.agents');
    }
}
