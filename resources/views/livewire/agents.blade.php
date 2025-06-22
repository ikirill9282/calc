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
              <form wire:submit.prevent="submit" action="{{ url('/agents/create') }}" class="flex flex-col gap-4">

                  <x-form.input wire:model="form.title" label="Название" inputName="title" :wire="true" />
                  <x-form.input wire:model="form.inn" label="ИНН/КПП" inputName="inn" class="input-numeric"
                      :wire="true" />
                  <x-form.input wire:model="form.ogrn" label="ОГРН/ОГРНИП" inputName="ogrn" class="input-numeric"
                      :wire="true" />

                  <x-form.address-dropdown id="address" name="address" label="Юридический адрес" :items="$this->getAddresses()"
                      :search="true" :value="$this->form['address']" />

                  <x-form.input wire:model="form.name" label="ФИО" inputName="name" :wire="true" />
                  <x-form.input wire:model="form.phone" label="Номер телефона" inputName="phone" x-mask="+7(999)999-99-99"
                      :wire="true" />
                  <x-form.input wire:model="form.email" label="Email" inputName="email" :wire="true"
                      type="email" />

                  <x-button type="submit">{{ $this->edit_mode ? 'Сохранить' : 'Добавить' }}</x-button>

                  @if($this->edit_mode)
                    <x-button wire:click="cancelEdit" type="button" outlined>Отменить</x-button>
                  @endif
              </form>
          </x-card>
      </div>
  </div>
</div>
