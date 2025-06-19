<div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10">

    <div class="flex flex-col justify-start items-stretch gap-10">
    @dump($this->fields)

        <x-form.fieldset set_title="Шаг 1" set_description="Выбор маршрута"
            set_class="{{ $this->isFieldDisabled(1) ? 'disabled' : '' }}" {{-- :set_loading="true" --}}>
            <div class="flex flex-col sm:gap-8">
                <x-form.dropdown id="warehouse_id" name="warehouse_id" placeholder="Откуда"
                    label="Склад отправления {{ config('app.name') }}:" :items="$this->getWarehouses()" :value="$this->getField('warehouse_id')"
                    :filter="true" />

                <x-form.service id="distributor_id" name="distributor_id" :items="$this->getDistributors()" />

                <x-form.dropdown id="distributor_center_id" name="distributor_center_id" placeholder="Адрес РЦ"
                    label="РЦ, в который будет доставлен груз" class="mt-6 sm:mt-0" :items="$this->getDistributorCenters()" :value="$this->getField('distributor_center_id')"
                    :filter="true" />
                {{-- <a
                    class="mt-4 sm:mt-0 flex justify-start items-center gap-2 transition hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                    <span>Нет нужного РЦ</span>
                    <span
                        class="rounded-full p-2 bg-primary-600/15 text-secondary-600 dark:text-secondary-400">@include('icons.question')</span>
                </a> --}}
            </div>
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 2" set_description="Доставки на РЦ"
            set_class="{{ $this->isFieldDisabled(2) ? 'disabled' : '' }}" {{-- set_loading="true" --}}>
            <x-form.datepicker label="Выберите, к какому числу доставить на РЦ" inputId="delivery_date"
                inputName="delivery_date" id="datepicker" :text="$this->getField('delivery_date')" />
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 3" set_description="Способ передачи груза"
            set_class="{{ $this->isFieldDisabled(3) ? 'disabled' : '' }}" {{-- set_loading="true" --}}>
            <fieldset class="flex flex-col gap-3">
                <div class="flex flex-wrap justify-start items-center group">
                    <x-form.radio name="transfer_method" id="transfer_method_receive" value="receive"
                        label="Самостоятельно привезти груз" :checked="in_array($this->getField('transfer_method'), ['receive']) ? 'checked' : ''" />
                    <div
                        class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['receive']) ? '' : 'hidden' }}">
                        <div
                            class="flex flex-col justify-start items-stretch gap-10 p-4 md:p-8 w-full rounded-lg bg-primary-50 dark:bg-primary-950 my-4">

                            <div class="flex flex-col gap-2">
                              <div class="flex flex-col sm:flex-row gap-2">
                                  <span>Адрес:</span>
                                  <span class="text-secondary-600 dark:text-secondary-400">{{ $this->getWarehouseAddress() }}</span>
                              </div>
                              <div class="flex flex-col sm:flex-row gap-2">
                                  <span>Телефон:</span>
                                  <span class="text-secondary-600 dark:text-secondary-400">
                                    <a href="tel:{{ $this->getWarehousePhone() }}">{{ $this->getWarehousePhone() }}</a>
                                  </span>
                              </div>
                            </div>

                            <x-form.datepicker pickerClass="w-full" inputId="transfer_method_receive.date"
                                inputName="transfer_method_receive.date" id="datepicker2" label="Укажите дату отгрузки"
                                labelClass="!bg-primary-50 dark:!bg-primary-950" :text="$this->getField('transfer_method_receive.date')" />

                            <div
                                class="cargo-date {{ $this->getField('transfer_method_receive.date') ? '' : 'hidden' }}">
                                <div
                                    class="flex justify-start items-center gap-3 w-full rounded-2xl p-3 text-white bg-sky-600">
                                    <span>@include('icons.check', ['width' => 40, 'height' => 40])</span>
                                    <span class="">Дата отгрузки на склад {{ config('app.name') }}: <span
                                            class="date">{{ $this->getField('transfer_method_receive.date') ?? '01.01.2025' }}</span>
                                        с 09:00 до 18:00</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-start items-center group">
                    <x-form.radio name="transfer_method" id="transfer_method_pick" value="pick"
                        label="Заберем груз от вас по адресу" :checked="in_array($this->getField('transfer_method'), ['pick']) ? 'checked' : ''" />
                    <div
                        class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['pick']) ? '' : 'hidden' }}">
                        <div
                            class="flex flex-col justify-start items-stretch gap-6 p-4 md:p-8 w-full rounded-lg bg-primary-50 dark:bg-primary-950 my-4">
                            <x-form.dropdown id="transfer_method_pick.address" name="transfer_method_pick.address"
                                label="Укажите адрес для подачи машины" labelClass="!bg-primary-50 dark:!bg-primary-950"
                                placeholder="г.Москва ..." :items="$this->getAddresses()" :search="true" />

                            <x-form.datepicker inputId="transfer_method_pick.date" inputName="transfer_method_pick.date"
                                label="Укажите дату отгрузки" labelClass="!bg-primary-50 dark:!bg-primary-950"
                                id="datepicker3" 
                                :text="$this->getField('transfer_method_pick.date')" 
                                />
                            {{-- <x-form.dropdown id="transfer_method_pick.time" name="transfer_method_pick.time"
                                label="Укажите время, когда сможете принять машину"
                                labelClass="!bg-primary-50 dark:!bg-primary-950" placeholder="Время..."
                                :items="collect($this->times)" /> --}}
                        </div>
                    </div>
                </div>
            </fieldset>
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 4" set_description="Тип доставки"
            set_class="{{ $this->isFieldDisabled(4) ? 'disabled' : '' }}">
            <div class="flex flex-col gap-6">
                <div class="checkbox-group flex flex-col justify-start items-start w-full">
                    <x-form.checkbox label="Коробки" id="boxes" name="boxes" :checked="$this->getField('boxes') ? 'checked' : ''" />
                    <div
                        class="infoblock boxes-item collapsed w-full {{ $this->getField('boxes') ? '' : 'hidden' }} rounded-lg p-2 sm:p-4 xl:p-8 mt-6 bg-primary-50 dark:bg-primary-950">

                        {{-- @foreach ($this->getField('boxes_items') as $key => $box) --}}
                            {{-- <div class="flex justify-between items-center mb-8">
                                <div class="text-2xl font-bold">Коробка {{ $key + 1 }}</div>
                                @if ($key > 0)
                                    <div wire:click="removeBox({{ $key }})"
                                        class="hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                                        @include('icons.trash')
                                    </div>
                                @endif
                            </div> --}}
                            <div class="flex flex-col gap-6 w-full">
                                <x-form.input 
                                    label="Количество"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" 
                                    inputName="boxes_data.count" 
                                    class="input-numeric" 
                                    :value="$this->getField('boxes_data.count')"
                                    :text="$this->getField('boxes_data.count')" 
                                  />
                                <x-form.input 
                                    label="Объем м3"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" 
                                    inputName="boxes_data.volume" 
                                    class="input-numeric" 
                                    :value="$this->getField('boxes_data.volume')"
                                    :text="$this->getField('boxes_data.volume')" 
                                  />
                                <x-form.input 
                                    label="Вес общий, кг"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" 
                                    inputName="boxes_data.weight" 
                                    class="input-numeric"
                                    :value="$this->getField('boxes_data.weight')"
                                    :text="$this->getField('boxes_data.weight')" 
                                  />
                                {{-- <x-form.input inputName="boxes_items.{{ $key }}.count" :value="$box['count']"
                                    :text="$box['count']" label="Кол-во коробок"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" class="input-numeric" />
                                <x-form.input inputName="boxes_items.{{ $key }}.weight" :value="$box['weight']"
                                    :text="$box['weight']" label="Вес общий, кг"
                                    labelClass="!bg-primary-50 dark:!bg-primary-950" class="input-numeric" /> --}}
                            </div>
                        {{-- @endforeach --}}


                        {{-- <x-button wire:click="addBox"
                            class="w-full !bg-primary-200 !text-primary-950 hover:!bg-primary-300 hover:!text-secondary-600 
                                  dark:!bg-primary-800 dark:hover:!bg-primary-700 dark:!text-primary-50 dark:hover:!text-secondary-400
                                ">
                            <p class="flex justify-center items-center gap-2">
                                <span>@include('icons.plus', ['width' => 20, 'height' => 20])</span>
                                <span>Добавить другие габариты</span>
                            </p>
                        </x-button> --}}
                    </div>
                </div>
                <div class="checkbox-group flex flex-col justify-start items-start w-full">
                    <x-form.checkbox label="Палеты" id="pallets" name="pallets" :checked="$this->getField('pallets') ? 'checked' : ''" />
                    <div
                        class="infoblock boxes-item collapsed w-full {{ $this->getField('pallets') ? '' : 'hidden' }} rounded-lg p-2 sm:p-4 xl:p-8 mt-6 bg-primary-50 dark:bg-primary-950">
                        <div class="flex flex-col gap-6 w-full ">
                            {{-- @foreach ($this->getField('pallets_items') as $key => $item) --}}
                                {{-- <div class="flex justify-between items-center">
                                    <div class="text-2xl font-bold">Палета {{ $key + 1 }}</div>
                                    @if ($key > 0)
                                        <div wire:click="removePallete({{ $key }})"
                                            class="hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                                            @include('icons.trash')
                                        </div>
                                    @endif
                                </div> --}}

                                <x-form.input 
                                  label="Кол-во"
                                  labelClass="!bg-primary-50 dark:!bg-primary-950" 
                                  class="input-numeric"
                                  inputName="pallets_data.count"
                                  :value="$this->getField('pallets_data.count')" 
                                  :text="$this->getField('pallets_data.count')"
                                />
                                <x-form.input
                                  label="Вес общий, кг"
                                  labelClass="!bg-primary-50 dark:!bg-primary-950" 
                                  class="input-numeric"
                                  inputName="pallets_data.weight"
                                  :value="$this->getField('pallets_data.weight')" 
                                  :text="$this->getField('pallets_data.weight')"
                                />
                            {{-- @endforeach --}}
                        </div>

                        {{-- <x-button wire:click="addPallete"
                            class="w-full !bg-primary-200 !text-primary-950 hover:!bg-primary-300 hover:!text-secondary-600 dark:hover:!text-secondary-400 dark:!bg-primary-800 dark:hover:!bg-primary-700 dark:!text-primary-50">
                            <p class="flex justify-center items-center gap-2">
                                <span>@include('icons.plus', ['width' => 20, 'height' => 20])</span>
                                <span>Добавить другие габариты</span>
                            </p>
                        </x-button> --}}
                    </div>
                </div>

                <div class="mt-4">
                    <x-form.textarea wire:model.live="fields.cargo_comment"  label="Комментарий"
                        placeholder="ИП Иванов И.И. 5 коробок / ИП Петров И.И. 5 коробок / ИП Васильев И.И. 5 коробок"
                        name="fields.cargo_comment"
                        ></x-form.textarea>
                </div>
            </div>
        </x-form.fieldset>

        <x-form.fieldset set_title="Шаг 5" set_description="Характер груза"
            set_class="{{ $this->isFieldDisabled(5) ? 'disabled' : '' }}">
            <div class="input-helper-group flex flex-col justify-start items-stretch gap-6">
                <x-form.input label="Какой тип груза будете перевозить" id="cargo_type" inputName="cargo_type"
                    :value="$this->getField('cargo_type')" :text="$this->getField('cargo_type')" />
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

        {{-- <x-form.fieldset set_title="Шаг 6" set_description="Тип доставки"
            set_class="{{ $this->isFieldDisabled(6) ? 'disabled' : '' }}">
            <div class="flex justify-start items-center gap-4">
                <x-form.checkbox label="Монопалета" name="delivery_type.monopalete" :checked="$this->getDeliveryType('monopalete') ? 'checked' : ''" />
                <x-form.checkbox label="Короб" name="delivery_type.box" :checked="$this->getDeliveryType('box') ? 'checked' : ''" />
                <x-form.checkbox label="QR код" name="delivery_type.qr" :checked="$this->getDeliveryType('qr') ? 'checked' : ''" />
            </div>
        </x-form.fieldset> --}}

        <x-form.fieldset set_description="Дополнительно: складские услуги" :title="false" set_loading="true"
            set_class="{{ $this->isFieldDisabled(7) ? 'disabled' : '' }}">
            @if (empty($this->getField('warehouse_id')))
              <p class="my-2">Для получения подробной информации выберите склад, из которого будет доставлен груз.</p>
            @else
              <div class="flex flex-col justify-start items-stretch gap-4">
                  <div class="flex justify-start items-center group" data-related="additional">
                      <div class="flex justify-start items-start flex-col gap-2 sm:gap-0 text-sm sm:text-md sm:flex-row">
                        <x-form.radio label="Палетирование" id="additional_palletizing" name="additional" />
                        <div class="sm:ml-8 font-bold">250 ₽ / шт</div>
                      </div>
                      <div class="grow"></div>
                      <x-form.counter name="palletizing" id="palletizing" :value="$this->getField('palletizing')" />
                  </div>
                  <div class="flex justify-start items-center group text-md" data-related="additional">
                      <div class="flex justify-start items-start flex-col gap-2 sm:gap-0 text-sm sm:text-md sm:flex-row">
                        <x-form.radio label="Поддон и палетирование" id="additional_pallete_palletizing"
                            name="additional" />
                        <div class="sm:ml-8 font-bold">650 ₽ / шт</div>
                      </div>
                      <div class="grow"></div>
                      <x-form.counter name="palletizing_pallet" id="palletizing_pallet" :value="$this->getField('palletizing_pallet')" />
                  </div>
              </div>
            @endif
        </x-form.fieldset>
    </div>
    <div class="">
        <x-details 
          :amount="$this->getAmount()"
          :pickamount="$this->getPickAmount()"
          :additional="$this->getAdditionalAmount()"
          :delivery="$this->getDeliveryAmount()"
          >
        </x-details>
    </div>
    
</div>
