@extends('layout.site')

@section('content')
    <div class="px-10">
        <h1 class="text-4xl font-semibold mb-12 py-6">Рассчитать мою доставку</h1>
        <div class="grid grid-cols-[1fr_400px] gap-10 mb-10">
            <div class="flex flex-col justify-start items-stretch gap-10">

                <x-form.fieldset set_title="Шаг 1" set_description="Выбор маршрута">
                    <div class="flex flex-col gap-8">
                        <x-form.dropdown id="from" name="from" placeholder="Откуда"
                            label="Склад отправления {{ config('app.name') }}:" />
                        <x-form.service id="service" name="service" />
                        <x-form.dropdown id="to" name="to" placeholder="Адрес РЦ"
                            label="РЦ, в который будет доставлен груз" />
                        <a
                            class="flex justify-start items-center gap-2 transition hover:cursor-pointer hover:text-secondary-600 dark:hover:text-secondary-400">
                            <span>Нет нужного РЦ</span>
                            <span
                                class="rounded-full p-2 bg-primary-600/15 text-secondary-600">@include('icons.question')</span>
                        </a>
                    </div>
                </x-form.fieldset>

                <x-form.fieldset set_title="Шаг 2" set_description="Доставки на РЦ">
                    <x-form.datepicker label="Выберите, к какому числу доставить на РЦ" inputId="datepicker" />
                </x-form.fieldset>

                <x-form.fieldset set_title="Шаг 3" set_description="Способ передачи груза">
                    <fieldset class="flex flex-col gap-3">
                        <div class="flex flex-wrap justify-start items-center hover:cursor-pointer group">
                            <input type="radio" id="receive" name="cargo" value="receive" class="peer w-0" checked />
                            <div class="w-3 h-3 mr-3 rounded-full transition
                                        ring-1 ring-offset-3 ring-offset-primary-900
                                      peer-checked:ring-secondary-400
                                    ">
                                      <div class="w-full h-full rounded-full scale-0 transition group-has-checked:scale-90 bg-primary-400 group-has-checked:bg-secondary-400"></div>
                                  </div>
                            <label for="receive" class="hover:cursor-pointer">Самостоятельно привезти груз</label>

                            <div class="infoblock w-full">
                              <div class="flex flex-col justify-start items-stretch gap-10 p-8 w-full rounded-lg bg-primary-950 my-4">
                                <div class="flex gap-2">
                                  <span>Адрес:</span>
                                  <span class="text-secondary-400">г.Екатеринбург, ул Хлебная дом напротив дома №1</span>
                                </div>
                                <x-form.datepicker pickerClass="w-full" inputId="datepicker2"></x-form.datepicker>
                                <div class="flex justify-start items-center gap-3 w-full rounded-2xl bg-sky-600 p-3">
                                  <span>@include('icons.check', ['width' => 40, 'height' => 40])</span>
                                  <span>Дата привоза груза на склад: 29 мая с 09:00 до 18:00</span>
                                </div>
                              </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-start items-center hover:cursor-pointer group">
                            <input type="radio" id="cargo" name="cargo" value="pick" class="peer w-0" />
                            <div class="w-3 h-3 mr-3 rounded-full transition
                                        ring-1 ring-offset-3 ring-offset-primary-900
                                      peer-checked:ring-secondary-400
                                    ">
                                      <div class="w-full h-full rounded-full scale-0 transition group-has-checked:scale-90 bg-primary-400 group-has-checked:bg-secondary-400"></div>
                                  </div>
                            <label for="cargo" class="hover:cursor-pointer">Заберем груз от вас по адресу</label>

                            <div class="infoblock w-full hidden">
                              <div class="flex flex-col justify-start items-stretch gap-6 p-8 w-full rounded-lg bg-primary-950 my-4">

                                <x-form.input 
                                  name="pick-address" 
                                  placeholder="Адрес отгрузки" 
                                  label="Укажите адрес отгрузки"
                                  labelClass="dark:!bg-primary-950"
                                />
                                <x-form.datepicker 
                                  name="pick-date"
                                  label="Укажите дату отгрузки"
                                  labelClass="dark:!bg-primary-950"
                                  inputId="datepicker3"
                                />
                                <x-form.dropdown 
                                  name="pick-time" 
                                  label="Укажите время, когда сможете принять машину" 
                                  labelClass="dark:!bg-primary-950" 
                                />

                              </div>
                            </div>
                        </div>
                    </fieldset>
                </x-form.fieldset>

                <x-form.fieldset set_title="Шаг 4" set_description="Тип доставки">
                  <div class="flex flex-col justify-start items-stretch w-full">
                    <input type="checkbox">
                    <div class="infoblock collapsed flex flex-col gap-6">
                      <x-form.input label="Высота см" />
                      <x-form.input label="Ширина см" />
                      <x-form.input label="Глубина см" />
                      <x-form.input label="Кол-во коробок" />
                      <x-form.input label="Вес общий, кг" />
                      <x-button>Добавить другие габариты</x-button>
                    </div>
                  </div>
                  <div class="flex flex-col justify-start items-stretch w-full">
                    <input type="checkbox">
                    <div class="infoblock collapsed flex flex-col gap-6">
                      <x-form.input label="Кол-во" />
                      <x-form.input label="Вес общий, кг" />
                      <x-button>Добавить другие габариты</x-button>
                    </div>
                  </div>
                </x-form.fieldset>

                <x-form.fieldset set_title="Шаг 5" set_description="Характер груза">
                  <x-form.input label="Какой тип груза будете перевозить" />
                </x-form.fieldset>

                <x-form.fieldset set_title="Шаг 6" set_description="Тип доставки">
                  <div class="flex justify-start items-center gap-4">
                    <div class="flex flex-row-reverse justify-start items-center gap-2">
                      <label for="#monopallete">Монопалета</label>
                      <input type="checkbox" name="monopallete" id="monopallete">
                    </div>
                    <div class="flex flex-row-reverse justify-start items-center gap-2">
                      <label for="#box">Короб</label>
                      <input type="checkbox" name="box" id="box">
                    </div>
                    <div class="flex flex-row-reverse justify-start items-center gap-2">
                      <label for="#monopallete">QR</label>
                      <input type="checkbox" name="qr" id="qr">
                    </div>
                  </div>
                </x-form.fieldset>

                <x-form.fieldset set_description="Дополнительно: складские услуги" :title="false">
                  
                </x-form.fieldset>
            </div>
            <div class="">
                <x-details></x-details>
            </div>
        </div>
    </div>
@endsection
