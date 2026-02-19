<div class="w-full">
  @if(!empty($this->messages))
    @foreach($this->messages as $k => $message)
      <div class="w-full time-message mb-4">
        <p class="py-4 px-6 bg-sky-500/25 border border-sky-600 text-sky-600 dark:text-sky-400">{{ $message }}</p>
      </div>
    @endforeach
  @endif
  <x-link href="{{ url('/') }}" class="inline-block sm:text-lg sm:mb-8">← Вернуться назад к&nbsp;заполнению заявки</x-link>
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
              <form wire:submit.prevent="submit" action="{{ url('/agents/create') }}" class="flex flex-col gap-4 bg-inherit">
                  <x-form.dropdown 
                    id="inn"
                    name="inn"
                    label="ИНН"
                    :items="$this->companies"
                    :searchable="true"
                    wire:model="form.inn"
                    :selectedKey="$this->company['key'] ?? null"
                    optionLabel="name"
                    optionValue="inn"
                    optionDescription="description"
                    optionKey="key"
                    autocomplete="off"
                    aria-autocomplete="off"
                    rp="form."
                    inputClass="input-numeric"
                    empty_text="ВВЕДИТЕ ИНН ВАШЕГО ИП ИЛИ ООО"
                  />

                  <x-form.wrap label="Название" name="title" :disabled="true" >
                    <x-form.input wire:model="form.title" name="title" readonly />
                  </x-form.wrap>

                  <x-form.wrap label="ОГРН/ОГРНИП" name="ogrn" :disabled="true" >
                    <x-form.input wire:model="form.ogrn" name="ogrn" class="input-numeric" readonly />
                  </x-form.wrap>

                  <div class="bg-inherit">
                    
                    <x-form.wrap label="Юридический адрес" name="address" :disabled="true">
                      <div x-data="{ v: @entangle('form.address') }" class="">
                        <div x-html="v" class="mt-1.5"></div>
                      </div>
                    </x-form.wrap>
                  </div>

                  <x-form.wrap label="ФИО" name="name" >
                    <x-form.input wire:model="form.name" name="name" />
                  </x-form.wrap>

                  <x-form.wrap label="Номер телефона" name="phone">
                    <x-form.input 
                      wire:model="form.phone" 
                      name="phone"
                      x-data="{}"
                      x-mask="+7(999)999-99-99"
                      x-init="
                        setTimeout(() => {
                          setTimeout(() => $el.dispatchEvent(new Event('focus')), 50)
                        }, 50)
                      "
                      placeholder="+7(999)999-99-99"
                     />
                  </x-form.wrap>

                  <x-form.wrap label="Email" name="email">
                    <x-form.input 
                      wire:model="form.email"
                      type="email"
                      name="email" 
                      x-data="{}"
                      placeholder="email@example.com"
                    />
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
