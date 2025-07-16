<div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10">   
  
      <div class="{{ $this->checkout ? 'flex' : 'hidden' }} flex-col gap-10">
          {{-- @dump($this->fields) --}}
          <x-form.fieldset :title="false" set_description="Контрагент" :set_loading="false">
            <div class="flex gap-4 flex-col md:flex-row bg-inherit">
              <x-form.dropdown 
                :items="\App\Models\Agent::where('user_id', auth()->user()?->id)->where('disabled', 0)->get()"
                label="Контрагент"
                name="agent_id"
                placeholder="Выберите контрагента..."
                wire:model="fields.agent_id"
                optionLabel="title"
                optionValue="id"
              />
              <x-button wire:click="goToAgents" outlined class="text-nowrap">
                Добавить контрагента
              </x-button>
            </div>

            @if($this->getField('agent_id'))
              <x-agent-view 
                :agent="\App\Models\Agent::find($this->getField('agent_id'))"
                :view="true"
              >
              </x-agent-view>
            @endif
          </x-form.fieldset>
          <x-form.fieldset :title="false" set_description="Способ оплаты" :set_loading="false">
            <div class="flex flex-col gap-4">
              <x-form.radio wire:model.live="fields.payment_method" groupClass="group/radio pm-group" name="payment_method" value="cash" label="Наличными при отправке" id="payment_method_cash" :checked="(($this->getField('payment_method') == 'cash') ? 'checked' : '')" />
              <x-form.radio wire:model.live="fields.payment_method" groupClass="group/radio pm-group" name="payment_method" value="bill" label="По счету" id="payment_method_bill" :checked="(($this->getField('payment_method') == 'bill') ? 'checked' : '')" />
            </div>
          </x-form.fieldset>
        </div>
        <div class="{{ $this->checkout ? 'hidden' : 'flex' }} flex-col justify-start items-stretch gap-10">
            
            {{-- @dump($this->fields) --}}
            {{-- @dump($this->fields, $this->fields['pallets_data'], $this->fields['boxes_data']) --}}
            
            <x-form.fieldset set_title="Шаг 1" set_description="Выбор маршрута"
                {{-- set_loading="false" --}}
                set_class="{{ $this->isFieldDisabled(1) ? 'disabled' : '' }}"
                >
                <div class="flex flex-col gap-8 bg-inherit">
                    <x-form.dropdown 
                      label="Склад отправления:"
                      name="warehouse_id"
                      placeholder="Откуда"
                      wire:model="fields.warehouse_id"
                      optionLabel="wh"
                      optionDescription="wh_address"
                      :items="$this->getWarehouses()"
                      :getOptionValueUsing="fn($item) => (($item['wh'] ?? '').' '.($item['wh_address'] ?? ''))"
                    />

                    <x-form.service 
                      name="distributor_id" 
                      :items="$this->getDistributors()"
                      wire:model="fields.distributor_id" 
                    />

                    <x-form.dropdown 
                      label="РЦ, в который будет доставлен груз"
                      name="distributor_center_id"
                      placeholder="Адрес РЦ"
                      wire:model="fields.distributor_center_id"
                      :items="$this->getDistributorCenters()"
                      optionValue="distributor_center"
                      optionLabel="distributor_center"
                      optionDescription="distributor_center"
                    />
                </div>
            </x-form.fieldset>

            <x-form.fieldset set_title="Шаг 2" set_description="Доставки на РЦ"
                set_class="{{ $this->isFieldDisabled(2) ? 'disabled' : '' }}" >
                <x-form.datepicker
                  id="datepicker"
                  name="fields.delivery_date"
                  label="Выберите, к какому числу доставить на РЦ"
                  wire:model.live="fields.delivery_date"
                />
            </x-form.fieldset>

            <x-form.fieldset set_title="Шаг 3" set_description="Способ передачи груза"
                set_class="{{ $this->isFieldDisabled(3) ? 'disabled' : '' }}">
                <fieldset class="flex flex-col gap-3">
                    <div class="flex flex-wrap justify-start items-center group/radio radio-box">
                        <x-form.radio 
                            name="transfer_method" 
                            id="transfer_method_receive" 
                            value="receive"
                            label="Самостоятельно привезти груз"
                            wire:model.live.debounce.350ms="fields.transfer_method"
                          />
                        <div class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['receive']) ? '' : 'hidden' }}">
                            <div
                                class="flex flex-col justify-start items-stretch gap-10 p-4 md:p-8 w-full rounded-lg bg-primary-50 dark:bg-primary-950 my-4">

                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <span>Адрес:</span>
                                        <span
                                            class="text-secondary-600 dark:text-secondary-400">{{ $this->getWarehouseAddress() }}</span>
                                    </div>
                                    {{-- <div class="flex flex-col sm:flex-row gap-2">
                                        <span>Телефон:</span>
                                        <span class="text-secondary-600 dark:text-secondary-400">
                                            <a
                                                href="tel:{{ $this->getWarehousePhone() }}">{{ $this->getWarehousePhone() }}</a>
                                        </span>
                                    </div> --}}
                                </div>

                                <x-form.datepicker 
                                    id="datepicker2"
                                    name="fields.transfer_method_receive.date"
                                    label="Укажите дату отгрузки"
                                    wire:model.live="fields.transfer_method_receive.date"
                                  />

                                <div
                                    class="cargo-date {{ $this->getField('transfer_method_receive.date') ? 'collapsed' : 'hidden' }}">
                                    <div
                                        class="flex justify-start items-center gap-3 w-full rounded-2xl p-3 text-white bg-sky-600">
                                        <span>@include('icons.check', ['width' => 40, 'height' => 40])</span>
                                        <span class="">Дата отгрузки на склад {{ str_contains(mb_strtolower($this->getField('warehouse_id')), 'симферополь') ? 'г. Симферополь' : 'г. Ростов-на-дону' }}: <span
                                                class="date">{{ $this->getField('transfer_method_receive.date') ?? '01.01.2025' }}</span>
                                            {{-- с 09:00 до 18:00 --}}
                                          </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-start items-center group/radio radio-box">
                        <x-form.radio 
                            name="transfer_method" 
                            id="transfer_method_pick" 
                            value="pick"
                            label="Заберем груз от вас по адресу" 
                            wire:model.live.debounce.350ms="fields.transfer_method"
                          />
                        <div class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['pick']) ? '' : 'hidden' }}">
                            <div
                                class="flex flex-col justify-start items-stretch gap-6 p-4 md:p-8 w-full rounded-lg bg-primary-50 dark:bg-primary-950 my-4">
                                {{-- @dump($this->fields['transfer_method_pick']) --}}
                                {{-- @dump($this->addresses) --}}
                                <x-form.dropdown 
                                    id="transfer_method_pick.address" 
                                    name="transfer_method_pick.address"
                                    label="Укажите адрес для подачи машины"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" 
                                    :items="$this->addresses" 
                                    wire:model="fields.transfer_method_pick.address"
                                    optionLabel="wh"
                                    optionValue="wh"
                                    :searchable="true"
                                    placeholder="г. Москва..."
                                  />

                                <x-form.datepicker 
                                    id="datepicker3"
                                    name="fields.transfer_method_pick.date"
                                    label="Укажите дату отгрузки"
                                    wire:model.live="fields.transfer_method_pick.date"
                                  />
                            </div>
                        </div>
                    </div>
                </fieldset>
            </x-form.fieldset>

            <x-form.fieldset set_title="Шаг 4" set_description="Тип доставки"
                set_class="{{ $this->isFieldDisabled(4) ? 'disabled' : '' }}">
                <div class="flex flex-col gap-6">
                    <div class="radio-box flex flex-col justify-start items-start w-full group/radio">
                        {{-- <x-form.checkbox label="Коробки" id="boxes" name="boxes" :checked="$this->getField('boxes') ? 'checked' : ''" /> --}}
                        <x-form.radio label="Коробки" id="fields.boxes" value="boxes" name="fields.cargo" wire:model.live.debounce.350ms="fields.cargo" />
                        <div
                            class="infoblock boxes-item collapsed w-full {{ $this->getField('cargo') == 'boxes' ? '' : 'hidden' }} rounded-lg p-2 sm:p-4 xl:p-8 mt-6 bg-primary-50 dark:bg-primary-950">
                            <div class="flex flex-col gap-6 w-full bg-inherit">
                                <x-form.wrap label="Количество" name="fields.boxes_data.count">
                                  <x-form.input class="input-numeric" name="fields.boxes_data.count" wire:model.live="fields.boxes_data.count" />
                                </x-form.wrap>
                                <x-form.wrap label="Объем м3" name="fields.boxes_data.volume">
                                  <x-form.input class="input-numeric" name="fields.boxes_data.volume" wire:model.live="fields.boxes_data.volume" />
                                </x-form.wrap>
                            </div>
                        </div>
                    </div>
                    <div class="radio-box flex flex-col justify-start items-start w-full group/radio" >
                        <x-form.radio label="Палеты" name="fields.cargo" value="pallets" id="fields.pallets" wire:model.live.debounce.350ms="fields.cargo" />
                        <div
                            class="infoblock boxes-item collapsed w-full {{ $this->getField('cargo') == 'pallets' ? '' : 'hidden' }} rounded-lg p-2 sm:p-4 xl:p-8 mt-6 bg-primary-50 dark:bg-primary-950">
                            <div class="flex flex-col gap-6 w-full bg-inherit">
                                <x-form.wrap label="Количество" name="fields.pallets_data.count">
                                  <x-form.input class="input-numeric" name="fields.pallets_data.count" wire:model.live="fields.pallets_data.count" />
                                </x-form.wrap>
                                {{-- <x-form.wrap label="Вес кг" name="fields.pallets_data.weight">
                                  <x-form.input class="input-numeric" name="fields.pallets_data.weight" wire:model.live="fields.pallets_data.weight" />
                                </x-form.wrap> --}}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-form.textarea wire:model.live="fields.cargo_comment" label="Комментарий"
                            placeholder="ИП Иванов И.И. 5 коробок / ИП Петров И.И. 5 коробок / ИП Васильев И.И. 5 коробок"
                            name="fields.cargo_comment"></x-form.textarea>
                    </div>
                </div>
            </x-form.fieldset>

            <x-form.fieldset set_title="Шаг 5" set_description="Характер груза"
                set_class="{{ $this->isFieldDisabled(5) ? 'disabled' : '' }}">
                <div class="input-helper-group flex flex-col justify-start items-stretch gap-6 bg-inherit">
                    <x-form.wrap label="Какой тип груза будете перевозить">
                      <x-form.input 
                        id="cargo_type" 
                        name="fields.cargo_type"
                        wire:model="fields.cargo_type"
                        aria-autocomplete="off"
                        autocomplete="off"
                        />
                    </x-form.wrap>
                    <div class="flex justify-start items-center gap-2">
                        <p class="text-primary-400">Например:</p>
                        <p class="flex justify-start items-center gap-2">
                            <span
                                class="inut-helper-item hover:cursor-pointer hover:text-secondary-600 hover:dark:text-secondary-400">Текстиль</span>
                            <span
                                class="inut-helper-item hover:cursor-pointer hover:text-secondary-600 hover:dark:text-secondary-400">Игрушки</span>
                        </p>
                    </div>
                </div>
            </x-form.fieldset>

            <x-form.fieldset set_description="Дополнительно: складские услуги" :title="false"
                set_class="{{ $this->isFieldDisabled(7) ? 'disabled' : '' }}">
                <div class="{{ empty($this->getField('warehouse_id')) ? '' : 'hidden' }}">
                    <p class="my-2">Для получения подробной информации выберите склад, из которого будет доставлен
                        груз.</p>
                </div>
                <div class="flex flex-col justify-start items-stretch gap-4 additional-box {{ empty($this->getField('warehouse_id')) ? 'hidden' : '' }}">
                    <div class="flex justify-start items-center group/radio radio-box" data-related="additional">
                        <div
                            class="flex justify-start items-start flex-col gap-2 sm:gap-0 text-sm sm:text-base sm:flex-row">
                            <x-form.radio 
                              label="Палетирование" 
                              id="additional_palletizing" 
                              name="palletizing_type"
                              value="single"
                              wire:model="fields.palletizing_type"
                            />
                            <div class="sm:ml-8 font-bold">250 ₽ / шт</div>
                        </div>
                        <div class="grow"></div>
                        <x-form.counter 
                          name="palletizing" 
                          id="palletizing" 
                          wire:model="fields.palletizing_count"
                          :count="($this->getField('palletizing_type') == 'single') ? $this->getField('palletizing_count'): 0" 
                        />
                    </div>
                    <div class="flex justify-start items-center group/radio radio-box" data-related="additional">
                        <div
                            class="flex justify-start items-start flex-col gap-2 sm:gap-0 text-sm sm:text-base sm:flex-row">
                            <x-form.radio 
                                label="Поддон и палетирование" 
                                id="additional_palletizing_pallet"
                                name="palletizing_type"
                                value="pallet"
                                wire:model="fields.palletizing_type"
                            />
                            <div class="sm:ml-8 font-bold">650 ₽ / шт</div>
                        </div>
                        <div class="grow"></div>
                        <x-form.counter 
                          name="palletizing_pallet" 
                          id="palletizing_pallet" 
                          wire:model="fields.palletizing_count"
                          :count="($this->getField('palletizing_type') == 'pallet') ? $this->getField('palletizing_count'): 0"
                        />
                    </div>
                </div>
            </x-form.fieldset>
        </div>

      <div class="">
        <x-details :order="$this->prepareOrder()">
            <x-button 
              wire:click.prevent="submit" 
              class="w-full 
                    {{ auth()->check() ? '' : 'open_auth' }} 
                    {{ $this->isFieldDisabled(7) ? 'pointer-events-none select-none !bg-primary-500' : '' }}
                    "
                  >
                    {{ $this->checkout ? 'Оформить' : 'Перейти к оформлению' }}
            </x-button>
            <x-button class="w-full" outlined>
              <div class="flex justify-center items-center gap-2">
                <span>@include('icons.download')</span>
                <a target="_blank" href="https://docs.google.com/spreadsheets/d/198VI0GjoaFRSdPzP5meYhBg4AqhnbS1unyywGThg0-o/edit?usp=sharing">Скачать прайс-лист</a>
              </div>
            </x-button>

            <x-slot:bot>
              @if(!empty($this->getField('delivery_date')))
                <x-card>
                  <div class="flex flex-col gap-2 w-full">
                    <div class="flex items-center gap-4">
                      <p class="w-2 h-2 ml-2 ring-4 ring-amber-600"></p>
                      <p>{{ \Illuminate\Support\Carbon::parse($this->getDeliveryDiff())->translatedFormat('d F') }} отправляется с терминала</p>
                    </div>
                    <div class="">
                      @include('icons.arrow', ['width' => 24, 'height' => 24])
                    </div>
                    <div class="flex items-center gap-4">
                      <p class="w-2 h-2 ml-2 ring-4 ring-secondary-600"></p>
                      <p>{{ \Illuminate\Support\Carbon::parse($this->getField('delivery_date'))->translatedFormat('d F') }} прибывает в РЦ</p>
                    </div>
                  </div>
                </x-card>
              @endif
            </x-slot:bot>
        </x-details>
    </div>

</div>
