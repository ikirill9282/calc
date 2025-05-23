@extends('layout.site')

@section('content')
  <div class="px-10">
    <h1 class="text-4xl font-semibold mb-12 py-6">Рассчитать мою доставку</h1>
    <div class="grid grid-cols-[1fr_400px] gap-10 mb-10">
      <div class="flex flex-col justify-start items-stretch gap-10">

        <x-form.fieldset 
          set_title="Шаг 1" 
          set_description="Выбор маршрута"
        >
          <div class="flex flex-col gap-4">
            <x-form.select id="from" name="from" placeholder="Откуда" />
            <x-form.service id="service" name="service" />
            <x-form.select id="from" name="from" placeholder="Адрес РЦ" />
          </div>
        </x-form.fieldset>

        <x-form.fieldset 
          set_title="Шаг 2" 
          set_description="Доставки на РЦ"
        >
        </x-form.fieldset>

        <x-form.fieldset 
          set_title="Шаг 3" 
          set_description="Способ передачи груза"
        >
        </x-form.fieldset>

        <x-form.fieldset 
          set_title="Шаг 4" 
          set_description="Тип доставки"
        >
        </x-form.fieldset>
        <x-form.fieldset 
          set_title="Шаг 5" 
          set_description="Характер груза"
        >
        </x-form.fieldset>
        <x-form.fieldset 
          set_title="Шаг 6" 
          set_description="Тип доставки"
        >
        </x-form.fieldset>
      </div>
      <div class="">
        <x-details></x-details>
      </div>
    </div>
  </div>
@endsection