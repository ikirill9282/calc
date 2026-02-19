<div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10 relative">
    {{-- Overlay прелоадер во время обработки формы --}}
    <div wire:loading.flex wire:target="submit"
        class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/55 backdrop-blur-[2px]">
        <div class="mx-4 w-full max-w-md rounded-2xl border border-primary-200 bg-white p-8 shadow-2xl dark:border-primary-700 dark:bg-primary-900">
            <div class="flex flex-col items-center gap-4 text-center">
                <div class="text-secondary-600 dark:text-secondary-400">
                    @include('icons.loading', ['width' => 50, 'height' => 50])
                </div>
                <p class="text-xl font-semibold text-primary-900 dark:text-primary-100">Обработка заявки...</p>
                <p class="text-sm text-primary-600 dark:text-primary-400">Пожалуйста, подождите</p>
            </div>
        </div>
    </div>

    <div class="{{ $this->checkout ? 'flex' : 'hidden' }} flex-col gap-10">
        <x-link wire:click.prevent="back" class="sm:text-lg sm:mb-8">← Вернуться назад к&nbsp;заполнению заявки</x-link>
        <x-form.fieldset :title="false" set_description="Контрагент" :set_loading="false">
            <div class="flex gap-4 flex-col md:flex-row bg-inherit">
                <x-form.dropdown :items="\App\Models\Agent::where('user_id', auth()->user()?->id)
                    ->where('disabled', 0)
                    ->get()" label="Контрагент" name="agent_id" placeholder="Выбрать свое ИП или ООО"
                    wire:model="fields.agent_id" optionLabel="title" optionValue="id" />
                <x-button wire:click="goToAgents" outlined class="text-nowrap">
                    Добавить свое ИП или ООО
                </x-button>
            </div>

            @if ($this->getField('agent_id'))
                <x-agent-view :agent="\App\Models\Agent::find($this->getField('agent_id'))" :view="true">
                </x-agent-view>
            @endif

        </x-form.fieldset>

        <x-form.fieldset :title="false" :set_description="$this->getPaymentMethodTitle()" :set_loading="false">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-4">
                    <x-form.radio wire:model.live="fields.payment_method" groupClass="group/radio pm-group"
                        name="payment_method" value="cash" label="Наличными при отправке" id="payment_method_cash"
                        :checked="$this->getField('payment_method') == 'cash' ? 'checked' : ''" />
                    <x-form.radio wire:model.live="fields.payment_method" groupClass="group/radio pm-group"
                        name="payment_method" value="bill" label="По счету" id="payment_method_bill"
                        :checked="$this->getField('payment_method') == 'bill' ? 'checked' : ''" />
                </div>

                @error('payment_method')
                    <span class="text-red-500 inline-block">{{ $message }}</span>
                @enderror
            </div>
        </x-form.fieldset>
    </div>
    <div class="{{ $this->checkout ? 'hidden' : 'flex' }} flex-col justify-start items-stretch gap-10">

        {{-- @dump($this->fields) --}}
        {{-- @dump($this->fields, $this->fields['transfer_method_receive'], $this->fields['transfer_method_pick']) --}}

        <x-form.fieldset set_title="Шаг 1" set_description="Выбор маршрута" {{-- set_loading="false" --}}
            set_class="{{ $this->isFieldDisabled(1) ? 'disabled' : '' }}">
            <div class="flex flex-col gap-8 bg-inherit">
                <x-form.dropdown label="Склад отправления:" name="warehouse_id" placeholder="Откуда"
                    wire:model="fields.warehouse_id" optionLabel="wh" optionDescription="wh_address" :items="$this->getWarehouses()"
                    :getOptionValueUsing="fn($item) => ($item['wh'] ?? '') . ' ' . ($item['wh_address'] ?? '')" />

                <x-form.service name="distributor_id" :items="$this->getDistributors()" wire:model="fields.distributor_id" />

                <x-form.dropdown label="РЦ, в который будет доставлен груз" name="distributor_center_id"
                    placeholder="Адрес РЦ" wire:model="fields.distributor_center_id" :items="$this->getDistributorCenters()"
                    optionValue="val" optionLabel="distributor_center" optionDescription="distributor_address" />

                @if((stripos($this->getField('distributor_id') ?? '', 'Ozon') !== false || stripos($this->getField('distributor_id') ?? '', 'ОЗОН') !== false) && stripos($this->getField('distributor_center_id') ?? '', 'Ростов-на-Дону') !== false)
                <div class="flex flex-col gap-2 w-full p-4 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm md:text-base font-medium">Уважаемые клиенты, будьте внимательны, доставка на РФЦ Ростов-на-Дону_2 недоступна!</p>
                    <p class="text-sm md:text-base">Мы осуществляем доставку только на РФЦ Ростов-на-Дону по адресу: Ростовская обл., Аксайский р-н, х. Ленина, Логопарк 5</p>
                </div>
                @endif
            </div>
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 2" set_description="Дата доставки на РЦ"
            set_class="{{ $this->isFieldDisabled(2) ? 'disabled' : '' }}">
            <x-form.datepicker id="datepicker" name="fields.delivery_date"
                label="Выберите, к какому числу доставить на РЦ" wire:model.live="fields.delivery_date" />
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 3" set_description="Способ передачи груза"
            set_class="{{ $this->isFieldDisabled(3) ? 'disabled' : '' }}">
            <fieldset class="flex flex-col gap-3">
                <div class="flex flex-wrap justify-start items-center group/radio radio-box">
                    <x-form.radio name="transfer_method" id="transfer_method_receive" value="receive"
                        label="Самостоятельно привезти груз" wire:model.live.debounce.350ms="fields.transfer_method" />
                    <div
                        class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['receive']) ? '' : 'hidden' }}">
                        <div
                            class="flex flex-col justify-start items-stretch gap-10 p-4 md:p-8 w-full bg-primary-50 dark:bg-primary-950 my-4">

                            <div class="flex flex-col gap-2">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <span>Адрес:</span>
                                    <span
                                        class="text-secondary-600 dark:text-secondary-400">{{ $this->getWarehouseAddress() }}</span>
                                </div>
                            </div>

                            <x-form.datepicker id="datepicker2" name="fields.transfer_method_receive.date"
                                label="Укажите дату отгрузки" wire:model.live="fields.transfer_method_receive.date" />

                            @if(stripos($this->getField('distributor_id') ?? '', 'Ozon') !== false || stripos($this->getField('distributor_id') ?? '', 'ОЗОН') !== false)
                            <div class="flex flex-col gap-3 w-full mt-4">
                                <div class="flex justify-start items-start gap-3 w-full p-4 text-white bg-amber-600">
                                    <span>@include('icons.info', ['width' => 40, 'height' => 40])</span>
                                    <span class="">При сдаче груза на склад ТК "82 регион" обязательно наличие ТН. Образец ТН можно скачать ниже. Пример заполнения ТН есть в Базе Знаний OZON. Накладная может понадобиться для решения спорных вопросов с OZON.</span>
                                </div>
                                <div class="pl-4">
                                    <a href="{{ asset('shablon-transportnoy-naklodnoy_1668418908.xlsx') }}" download class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 underline font-medium">
                                        Скачать шаблон
                                    </a>
                                </div>
                            </div>
                            @endif

                            <div
                                class="cargo-date {{ $this->getField('transfer_method_receive.date') ? 'collapsed' : 'hidden' }}">
                                <div class="w-full p-4 text-white bg-sky-600">
                                  <div class="flex justify-start items-center gap-3">
                                    <div>@include('icons.check', ['width' => 40, 'height' => 40])</div>
                                    <div class="flex flex-col">
                                      <div class="">Дата отгрузки на склад {{ $this->getCity() }}: <span
                                            class="date">{{ $this->getField('transfer_method_receive.date') ?? '01.01.2025' }}</span></div>
                                      <div class="">Прием груза на складе до 16:00</div>
                                    </div>
                                  </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-start items-center group/radio radio-box">
                    <x-form.radio name="transfer_method" id="transfer_method_pick" value="pick"
                        label="Заберем груз от вас по адресу" wire:model.live.debounce.350ms="fields.transfer_method" />
                    <div
                        class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['pick']) ? '' : 'hidden' }}">
                        <div
                            class="flex flex-col justify-start items-stretch gap-6 p-4 md:p-8 w-full bg-primary-50 dark:bg-primary-950 my-4">
                            
                            <div class="bg-inherit">
                                <x-form.dropdown id="transfer_method_pick.address" name="transfer_method_pick.address"
                                    label="Укажите адрес для подачи машины"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" :items="$this->addresses"
                                    wire:model="fields.transfer_method_pick.address" optionLabel="wh"
                                    optionValue="wh" :searchable="true" placeholder="В формате: город, улица, дом..."
                                    empty_text="Начните вводить адрес..." />
                                <div class="text-xs sm:text-sm mt-2">Если нужно адреса нет в списке, попробуйте ввести
                                    только город и улицу</div>

                                
                                @error('transfer_method_pick.address')
                                  <div class="mt-3 text-red-500">
                                    {{ $message }}
                                  </div>
                                @enderror
                            </div>

                            <x-form.datepicker id="datepicker3" name="fields.transfer_method_pick.date"
                                label="Укажите дату отгрузки" wire:model.live="fields.transfer_method_pick.date" />

                            @if(stripos($this->getField('distributor_id') ?? '', 'Ozon') !== false || stripos($this->getField('distributor_id') ?? '', 'ОЗОН') !== false)
                            <div class="flex flex-col gap-3 w-full mt-4">
                                <div class="flex justify-start items-start gap-3 w-full p-4 text-white bg-amber-600">
                                    <span>@include('icons.info', ['width' => 40, 'height' => 40])</span>
                                    <span class="">При сдаче груза на склад ТК "82 регион" обязательно наличие ТН. Образец ТН можно скачать ниже. Пример заполнения ТН есть в Базе Знаний OZON. Накладная может понадобиться для решения спорных вопросов с OZON.</span>
                                </div>
                                <div class="pl-4">
                                    <a href="{{ asset('shablon-transportnoy-naklodnoy_1668418908.xlsx') }}" download class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 underline font-medium">
                                        Скачать шаблон
                                    </a>
                                </div>
                            </div>
                            @endif
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
                    <x-form.radio label="Коробки" id="fields.boxes" value="boxes" name="fields.cargo"
                        wire:model.live.debounce.350ms="fields.cargo" />
                    <div
                        class="infoblock boxes-item collapsed w-full {{ $this->getField('cargo') == 'boxes' ? '' : 'hidden' }} p-2 sm:p-4 xl:p-8 mt-6 bg-primary-50 dark:bg-primary-950">
                        <div class="flex flex-col gap-6 w-full bg-inherit">
                            <x-form.wrap label="Количество" name="fields.boxes_data.count">
                                <x-form.input class="input-numeric" name="fields.boxes_data.count"
                                    wire:model.live="fields.boxes_data.count" />
                            </x-form.wrap>
                            <x-form.wrap label="Объем м3" name="fields.boxes_data.volume">
                                <x-form.input class="input-numeric" name="fields.boxes_data.volume"
                                    wire:model.live="fields.boxes_data.volume"
                                    x-on:input="$event.target.value = $event.target.value.replace(',', '.')" />
                            </x-form.wrap>
                            <x-form.wrap label="Вес" name="fields.boxes_data.weight">
                                <x-form.input class="input-numeric" name="fields.boxes_data.weight"
                                    wire:model.live="fields.boxes_data.weight"
                                    x-on:input="$event.target.value = $event.target.value.replace(',', '.')" />
                            </x-form.wrap>
                            @if(stripos($this->getField('distributor_id') ?? '', 'Ozon') !== false || stripos($this->getField('distributor_id') ?? '', 'ОЗОН') !== false)
                            <x-form.wrap label="Номер поставки" name="fields.ozon_shipment_number">
                                <div class="flex items-center gap-0 w-full" wire:ignore>
                                    <span class="inline-flex items-center min-h-9 px-3 bg-primary-100 dark:bg-primary-800 border border-primary-200 dark:border-primary-700 rounded-l-md text-primary-900 dark:text-primary-100 font-mono select-none">20000</span>
                                    <div x-data="{ val: @js($this->getField('ozon_shipment_number_suffix') ?? '') }" class="flex-1 min-w-0">
                                        <input type="text"
                                            x-model="val"
                                            x-on:input="val = val.replace(/\D/g, '').slice(0, 8)"
                                            x-on:blur="$wire.set('fields.ozon_shipment_number_suffix', val)"
                                            maxlength="8" inputmode="numeric" placeholder="_________"
                                            class="outline-0 h-full w-full min-h-9 rounded-l-none border-l-0 border border-primary-200 dark:border-primary-700 rounded-r-md px-3 py-2 bg-white dark:bg-primary-900 text-primary-900 dark:text-primary-100" />
                                    </div>
                                </div>
                            </x-form.wrap>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="radio-box flex flex-col justify-start items-start w-full">
                    <x-form.radio label="Палеты" name="fields.cargo" value="pallets" id="fields.pallets"
                        wire:model.live.debounce.350ms="fields.cargo" />
                    <div
                        class="infoblock boxes-item collapsed w-full {{ $this->getField('cargo') == 'pallets' ? '' : 'hidden' }} p-2 sm:p-4 xl:p-8 mt-6 bg-primary-50 dark:bg-primary-950">
                        <div class="flex flex-col gap-6 w-full bg-inherit">
                            <x-form.wrap label="Количество" name="fields.pallets_data.count">
                                <x-form.input class="input-numeric" name="fields.pallets_data.count"
                                    wire:model.live.debounce.500ms="fields.pallets_data.count" />
                            </x-form.wrap>
                            <x-form.wrap label="Количество коробок" name="fields.pallets_data.boxcount">
                                <x-form.input class="input-numeric" name="fields.pallets_data.boxcount"
                                    wire:model.live.debounce.500ms="fields.pallets_data.boxcount" />
                            </x-form.wrap>
                            <x-form.wrap label="Общий объем " name="fields.pallets_data.volume">
                                <x-form.input class="input-numeric" name="fields.pallets_data.volume"
                                    wire:model.live.debounce.500ms="fields.pallets_data.volume"
                                    x-on:input="$event.target.value = $event.target.value.replace(',', '.')" />
                            </x-form.wrap>
                            <x-form.wrap label="Общий вес" name="fields.pallets_data.weight">
                                <x-form.input class="input-numeric" name="fields.pallets_data.weight"
                                    wire:model.live.debounce.500ms="fields.pallets_data.weight"
                                    x-on:input="$event.target.value = $event.target.value.replace(',', '.')" />
                            </x-form.wrap>
                            @if(stripos($this->getField('distributor_id') ?? '', 'Ozon') !== false || stripos($this->getField('distributor_id') ?? '', 'ОЗОН') !== false)
                            <x-form.wrap label="Номер поставки" name="fields.ozon_shipment_number">
                                <div class="flex items-center gap-0 w-full" wire:ignore>
                                    <span class="inline-flex items-center min-h-9 px-3 bg-primary-100 dark:bg-primary-800 border border-primary-200 dark:border-primary-700 rounded-l-md text-primary-900 dark:text-primary-100 font-mono select-none">20000</span>
                                    <div x-data="{ val: @js($this->getField('ozon_shipment_number_suffix') ?? '') }" class="flex-1 min-w-0">
                                        <input type="text"
                                            x-model="val"
                                            x-on:input="val = val.replace(/\D/g, '').slice(0, 8)"
                                            x-on:blur="$wire.set('fields.ozon_shipment_number_suffix', val)"
                                            maxlength="8" inputmode="numeric" placeholder="_________"
                                            class="outline-0 h-full w-full min-h-9 rounded-l-none border-l-0 border border-primary-200 dark:border-primary-700 rounded-r-md px-3 py-2 bg-white dark:bg-primary-900 text-primary-900 dark:text-primary-100" />
                                    </div>
                                </div>
                            </x-form.wrap>
                            @endif
                            {{-- <div class="text-xs sm:text-sm">Если вес 1 паллеты превышает 400 кг , расчет производится
                                индивидуально, предварительная стоимость указана при условии, что вес каждой паллеты не
                                превышает 400кг</div> --}}
                        </div>

                        <div class="mt-4">
                            <div class="flex justify-start items-center group/radio radio-box ml-1"
                                data-related="additional">
                                <div
                                    class="flex justify-start items-start flex-col gap-2 sm:gap-0 text-sm sm:text-base sm:flex-row">
                                    <x-form.radio label="Палетирование" id="additional_palletizing"
                                        name="palletizing_type" value="single"
                                        wire:model.live="fields.palletizing_type"
                                        x-on:change="() => {
                                          console.log({{ $this->fields['palletizing_count'] ?? 0 }});
                                          if ({{ $this->fields['palletizing_count'] ?? 0 }} == 0) {
                                            $dispatch('setField', {
                                              name: 'palletizing_count',
                                              value: 1,
                                            });
                                          }
                                        }" />
                                    <div class="sm:ml-8 font-bold">800 ₽ / шт</div>
                                </div>
                                <div class="grow"></div>
                                <x-form.counter name="palletizing" id="palletizing"
                                    wire:model="fields.palletizing_count" :count="$this->getField('palletizing_type') == 'single'
                                        ? $this->getField('palletizing_count')
                                        : 0" />
                            </div>
                            <div class="flex justify-start items-center gap-3 w-full p-4 mt-4 text-white bg-amber-600">
                                <span>@include('icons.info', ['width' => 40, 'height' => 40])</span>
                                <span class="">Важно! При отправке груза на паллетах, необходимо самостоятельно
                                    запаллетировать груз, либо заказать услугу у нас.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <x-form.textarea wire:model.live="fields.cargo_comment" label="Комментарий"
                        placeholder="Оставьте любые примечания по грузу, доставке и забору"
                        name="fields.cargo_comment"></x-form.textarea>
                </div>
            </div>
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 5" set_description="Характер груза"
            set_class="{{ $this->isFieldDisabled(5) ? 'disabled' : '' }}">
            <div class="input-helper-group flex flex-col justify-start items-stretch gap-6 bg-inherit">
                <x-form.wrap label="Какой тип груза будете перевозить">
                    <x-form.input id="cargo_type" name="fields.cargo_type" wire:model="fields.cargo_type"
                        aria-autocomplete="off" autocomplete="off" />
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
    </div>

    {{-- DETAILS --}}
    <div class="">
        <x-details :order="$this->prepareOrder()">
            <x-button wire:click.prevent="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="w-full relative
                    {{ auth()->check() ? '' : 'open_auth' }} 
                    {{ $this->isFieldDisabled(7) ? 'pointer-events-none select-none !bg-primary-500' : '' }}
                    ">
                <span wire:loading.remove wire:target="submit">
                    {{ $this->checkout ? 'Оформить' : 'Перейти к оформлению' }}
                </span>
                <span wire:loading wire:target="submit" class="flex items-center justify-center gap-2">
                    <span class="inline-block">
                        @include('icons.loading', ['width' => 20, 'height' => 20])
                    </span>
                    <span>Обработка...</span>
                </span>
            </x-button>
            <x-button class="w-full !p-0" outlined>
                <div class="flex justify-center items-center gap-2">
                    {{-- <span>@include('icons.download')</span> --}}
                    <a target="_blank"
                        class="!p-3 !w-full"
                        href="https://docs.google.com/spreadsheets/d/198VI0GjoaFRSdPzP5meYhBg4AqhnbS1unyywGThg0-o/edit?usp=sharing">
                        Прайс-лист
                    </a>
                </div>
            </x-button>

            <x-slot:bot>
                @if (!empty($this->getField('delivery_date')) && $this->isValidCarbonDate($this->getField('delivery_date')))
                    <x-card>
                        <div class="flex flex-col gap-2 w-full">
                            @if (!empty($this->getPostDate()))
                                <div class="flex items-center gap-4">
                                    <p class="w-2 h-2 ml-2 ring-4 ring-amber-600"></p>
                                    <p>{{ \Illuminate\Support\Carbon::parse($this->getPostDate())->translatedFormat('d F') }}
                                        отправляется с терминала</p>
                                </div>
                                <div class="">
                                    @include('icons.arrow', ['width' => 24, 'height' => 24])
                                </div>
                            @endif
                            <div class="flex items-center gap-4">
                                <p class="w-2 h-2 ml-2 ring-4 ring-secondary-600"></p>
                                <p>{{ \Illuminate\Support\Carbon::parse($this->getField('delivery_date'))->translatedFormat('d F') }}
                                    прибывает в РЦ</p>
                            </div>
                        </div>
                    </x-card>
                @endif
            </x-slot:bot>
        </x-details>
    </div>

</div>
