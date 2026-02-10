@props([
  'name' => '',
  'label' => '',
])

<div 
  class="datepicker-group relative bg-inherit {{ $pickerClass ?? '' }}"
  data-name="{{ $name ?? '' }}"
  >
  <div class="datepicker-icon absolute top-[50%] left-6 translate-y-[-50%] dark:text-primary-500 z-10 hover:cursor-pointer">
    @include('icons.calendar')
  </div>
  
  <x-form.wrap label="{{ $label }}" name="{{ $name }}">
    <x-form.input
      class="pl-16 datepicker"
      autocomplete="off"
      aria-autocomplete="off"
      data-datepicker="{{ $name }}"
      readonly
      {{ $attributes }}
      />
  </x-form.wrap>
</div>