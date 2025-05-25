<div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10">
  <div class="flex flex-col justify-start items-stretch gap-10">
      {{-- @dump($this->fields) --}}

      <x-form.fieldset set_title="Шаг 1" 
        set_description="Выбор маршрута" 
        set_class="{{ $this->isFieldDisabled(1) ? 'disabled' : '' }}"
        :set_loading="true"
        >
          <div class="flex flex-col sm:gap-8">
              <x-form.dropdown 
                  id="warehouse_id" 
                  name="warehouse_id" 
                  placeholder="Откуда"
                  label="Склад отправления {{ config('app.name') }}:"
                  :items="$this->getWarehouses()"
                  :value="$this->getField('warehouse_id')"
                  />

              <x-form.service 
                id="distributor_id" 
                name="distributor_id"
                :items="$this->getDistributors()"
              />

              <x-form.dropdown 
                  id="distributor_center_id" 
                  name="distributor_center_id" 
                  placeholder="Адрес РЦ"
                  label="РЦ, в который будет доставлен груз" 
                  class="mt-6 sm:mt-0"
                  :items="$this->getDistributorCenters()"
                  :value="$this->getField('distributor_center_id')"
                  />
              <a
                  class="mt-4 sm:mt-0 flex justify-start items-center gap-2 transition hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                  <span>Нет нужного РЦ</span>
                  <span class="rounded-full p-2 bg-primary-600/15 text-secondary-600 dark:text-secondary-400">@include('icons.question')</span>
              </a>
          </div>
      </x-form.fieldset>

      <x-form.fieldset set_title="Шаг 2" 
        set_description="Доставки на РЦ" 
        set_class="{{ $this->isFieldDisabled(2) ? 'disabled' : '' }}"
        set_loading="true"
        >
          <x-form.datepicker 
            label="Выберите, к какому числу доставить на РЦ" 
            inputId="delivery_date"
            inputName="delivery_date"
            id="datepicker"
            :text="$this->getField('delivery_date')"
            
          />
      </x-form.fieldset>

      <x-form.fieldset set_title="Шаг 3" 
        set_description="Способ передачи груза" 
        set_class="{{ $this->isFieldDisabled(3) ? 'disabled' : '' }}"
        set_loading="true"
        >
          <fieldset class="flex flex-col gap-3">
              <div 
                  class="flex flex-wrap justify-start items-center group"
                >
                  <x-form.radio 
                    name="transfer_method"
                    id="transfer_method_receive"
                    value="receive"
                    label="Самостоятельно привезти груз"
                    :checked="in_array($this->getField('transfer_method'), ['receive']) ? 'checked' : ''"
                  />
                  <div class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['receive']) ? '' : 'hidden' }}">
                    <div class="flex flex-col justify-start items-stretch gap-10 p-4 md:p-8 w-full rounded-lg bg-primary-50 dark:bg-primary-950 my-4">
                      
                      <div class="flex flex-col sm:flex-row gap-2">
                        <span>Адрес:</span>
                        <span class="text-secondary-600 dark:text-secondary-400">г.Екатеринбург, ул Хлебная дом напротив дома №1</span>
                      </div>
                      
                      <x-form.datepicker 
                        pickerClass="w-full" 
                        inputId="transfer_method.receive.date"
                        inputName="transfer_method.receive.date"
                        id="datepicker2" 
                        label="Укажите дату отгрузки" 
                        labelClass="!bg-primary-50 dark:!bg-primary-950"
                        :text="$this->getField('transfer_method.receive.date')"
                      />
                      
                      <div class="cargo-date {{ $this->getField('transfer_method.receive.date') ? '' : 'hidden' }}">
                        <div class="flex justify-start items-center gap-3 w-full rounded-2xl p-3 text-white bg-sky-600">
                          <span>@include('icons.check', ['width' => 40, 'height' => 40])</span>
                          <span class="">Дата отгрузки на склад {{ config('app.name') }}: <span class="date">01.01.2025</span> с 09:00 до 18:00</span>
                        </div>
                      </div>

                    </div>
                  </div>
              </div>

              <div 
                  class="flex flex-wrap justify-start items-center group"
                >
                  <x-form.radio 
                    name="transfer_method"
                    id="transfer_method_pick"
                    value="pick"
                    label="Заберем груз от вас по адресу"
                    :checked="in_array($this->getField('transfer_method'), ['pick']) ? 'checked' : ''"
                  />
                  <div class="infoblock w-full {{ in_array($this->getField('transfer_method'), ['pick']) ? '' : 'hidden' }}">
                    <div class="flex flex-col justify-start items-stretch gap-6 p-4 md:p-8 w-full rounded-lg bg-primary-50 dark:bg-primary-950 my-4">
                      <x-form.dropdown
                        id="transfer_method.pick.address" 
                        name="transfer_method.pick.address" 
                        label="Укажите адрес для подачи машины"
                        labelClass="!bg-primary-50 dark:!bg-primary-950"
                        placeholder="г.Москва ..."
                        :items="$this->getAddresses()"
                      />

                      <x-form.datepicker 
                        inputId="transfer_method.pick.date"
                        inputName="transfer_method.pick.date"
                        label="Укажите дату отгрузки"
                        labelClass="!bg-primary-50 dark:!bg-primary-950"
                        id="datepicker3"
                      />
                      
                      <x-form.dropdown
                        id="transfer_method.pick.time" 
                        name="transfer_method.pick.time" 
                        label="Укажите время, когда сможете принять машину" 
                        labelClass="!bg-primary-50 dark:!bg-primary-950"
                        placeholder="Время..."
                        :items="collect($this->times)"
                      />
                    </div>
                  </div>
              </div>
          </fieldset>
      </x-form.fieldset>

      <x-form.fieldset set_title="Шаг 4" set_description="Тип доставки" set_class="{{ $this->isFieldDisabled(4) ? 'disabled' : '' }}">
        <div class="flex flex-col gap-6">
          <div class="checkbox-group flex flex-col justify-start items-start w-full">
            <x-form.checkbox
              label="Коробки"
              id="boxes"
            />
            <div class="infoblock collapsed w-full hidden rounded-lg p-8 mt-6 bg-primary-50 dark:bg-primary-950">
              <div class="flex flex-col gap-6 w-full">
                <x-form.input label="Высота см" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-form.input label="Ширина см" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-form.input label="Глубина см" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-form.input label="Кол-во коробок" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-form.input label="Вес общий, кг" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-button class="!bg-primary-200 !text-primary-950 hover:!bg-primary-300 hover:!text-secondary-600 
                                dark:!bg-primary-800 dark:hover:!bg-primary-700 dark:!text-primary-50 dark:hover:!text-secondary-400
                              ">
                  <p class="flex justify-center items-center gap-2">
                    <span>@include('icons.plus', ['width' => 20, 'height' => 20])</span>
                    <span>Добавить другие габариты</span>
                  </p>
                </x-button>
              </div>
            </div>
          </div>
          <div class="checkbox-group flex flex-col justify-start items-start w-full">
            <x-form.checkbox 
              label="Палеты"
              id="pallets"
            />
            <div class="infoblock collapsed w-full hidden rounded-lg p-8 mt-6 bg-primary-50 dark:bg-primary-950">
              <div class="flex flex-col gap-6 w-full">
                <x-form.input label="Кол-во" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-form.input label="Вес общий, кг" labelClass="!bg-primary-50 dark:!bg-primary-950" />
                <x-button class="!bg-primary-200 !text-primary-950 hover:!bg-primary-300 hover:!text-secondary-600 dark:hover:!text-secondary-400 dark:!bg-primary-800 dark:hover:!bg-primary-700 dark:!text-primary-50">
                  <p class="flex justify-center items-center gap-2">
                    <span>@include('icons.plus', ['width' => 20, 'height' => 20])</span>
                    <span>Добавить другие габариты</span>
                  </p>
                </x-button>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <x-form.textarea label="Комментарий" placeholder="ИП Иванов И.И. 5 коробок / ИП Петров И.И. 5 коробок / ИП Васильев И.И. 5 коробок"></x-form.textarea>
          </div>
        </div>
      </x-form.fieldset>

      <x-form.fieldset set_title="Шаг 5" set_description="Характер груза" set_class="{{ $this->isFieldDisabled(5) ? 'disabled' : '' }}">
        <div class="input-helper-group flex flex-col justify-start items-stretch gap-6">
          <x-form.input label="Какой тип груза будете перевозить" id="cargo_type" />
          <div class="flex justify-start items-center gap-2">
            <p class="text-primary-400">Например:</p>
            <p class="flex justify-start items-center gap-2">
              <span class="inut-helper-item hover:cursor-pointer hover:text-secondary-600 hover:dark:text-secondary-400">Текстиль</span>
              <span class="inut-helper-item hover:cursor-pointer hover:text-secondary-600 hover:dark:text-secondary-400">Игрушки</span>
            </p>
          </div>
        </div>                  
      </x-form.fieldset>

      <x-form.fieldset set_title="Шаг 6" set_description="Тип доставки" set_class="{{ $this->isFieldDisabled(6) ? 'disabled' : '' }}">
        <div class="flex justify-start items-center gap-4">
          <x-form.checkbox
            label="Монопалета"
          />
          <x-form.checkbox
            label="Короб"
          />
          <x-form.checkbox
            label="QR код"
          />
        </div>
      </x-form.fieldset>

      <x-form.fieldset set_description="Дополнительно: складские услуги" :title="false" set_class="{{ $this->isFieldDisabled(7) ? 'disabled' : '' }}">
        <p class="my-2">Для получения подробной информации выберите склад, из которого будет доставлен груз.</p>
        <div class="flex flex-col justify-start items-stretch gap-4" >
          <div class="flex justify-start items-center group" data-related="additional" >
            <x-form.radio label="Палетирование" id="additional_palletizing" name="additional" />
            <div class="grow"></div>
            <x-form.counter />
          </div>
          <div class="flex justify-start items-center group" data-related="additional" >
            <x-form.radio label="Поддон и палетирование" id="additional_pallete_palletizing" name="additional" />
            <div class="grow"></div>
            <x-form.counter />
          </div>
        </div>
      </x-form.fieldset>
  </div>
  <div class="">
      <x-details></x-details>
  </div>

</div>