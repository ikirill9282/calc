<div class="w-full">
  @if(!empty($this->messages))
    @foreach($this->messages as $k => $message)
      <div class="w-full time-message mb-4">
        <p class="py-4 px-6 bg-sky-500/25 rounded-lg border border-sky-600 text-sky-600 dark:text-sky-400">{{ $message }}</p>
      </div>
    @endforeach
  @endif
  <div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_1fr] gap-5 2xl:gap-10">
      <div class="flex flex-col gap-4" id="agents-table">
          @if ($this->agents->isNotEmpty())
            @foreach ($this->agents as $agent)          
                <x-agent-view
                  :agent="$agent"
                />
            @endforeach
          @else
              <div class="">Нет добавленных контрагентов</div>
          @endif
      </div>
      <div>
          <x-card>
              @dump($this->form)
              <form wire:submit.prevent="submit" action="{{ url('/agents/create') }}" class="flex flex-col gap-4 bg-inherit">
                  {{-- <x-form.wrap label="Название" name="title" >
                    <x-form.input wire:model="form.title" name="title" />
                  </x-form.wrap> --}}
                  <x-form.dropdown 
                    id="title"
                    name="title"
                    label="Название"
                    :items="$this->companies"
                    :searchable="true"
                    wire:model="form.title"
                    :selectedKey="$this->company['key'] ?? null"
                    optionLabel="name"
                    optionValue="name"
                    optionDescription="description"
                    optionKey="key"
                    autocomplete="off"
                    aria-autocomplete="off"
                    rp="form."
                  />

                  <x-form.wrap label="ИНН/КПП" name="inn" >
                    <x-form.input wire:model="form.inn" name="inn" class="input-numeric" />
                  </x-form.wrap>

                  <x-form.wrap label="ОГРН/ОГРНИП" name="ogrn" >
                    <x-form.input wire:model="form.ogrn" name="ogrn" class="input-numeric" />
                  </x-form.wrap>

                  <x-form.dropdown 
                    id="address"
                    name="address"
                    label="Юридический адрес"
                    :items="$this->addresses"
                    wire:model="form.address"
                    optionLabel="wh"
                    optionValue="wh"
                    :searchable="true"
                    autocomplete="off"
                    aria-autocomplete="off"
                    rp="form."
                  />

                  <x-form.wrap label="ФИО" name="name" >
                    <x-form.input wire:model="form.name" name="name" />
                  </x-form.wrap>

                  <x-form.wrap label="Номер телефона" name="phone">
                    <x-form.input wire:model="form.phone" name="phone" x-mask="+7(999)999-99-99" />
                  </x-form.wrap>

                  <x-form.wrap label="Email" name="email">
                    <x-form.input wire:model="form.email" name="email" />
                  </x-form.wrap>

                  <x-button type="submit">{{ $this->edit_mode ? 'Сохранить' : 'Добавить' }}</x-button>

                  @if($this->edit_mode)
                    <x-button wire:click="cancelEdit" type="button" outlined>Отменить</x-button>
                  @endif
              </form>
          </x-card>
      </div>
  </div>
</div>
