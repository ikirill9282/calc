@props([
  'name' => '',
  'label' => '',
])

<div 
  class="datepicker-group relative bg-inherit {{ $pickerClass ?? '' }}"
  data-name="{{ $name ?? '' }}"
  >
  <div class="absolute top-[50%] left-6 translate-y-[-50%] dark:text-primary-500 z-10">
    @include('icons.calendar')
  </div>
  
  <x-forms.wrap label="{{ $label }}" name="{{ $name }}">
    <x-forms.input
      class="pl-16 datepicker"
      autocomplete="off"
      aria-autocomplete="off"
      data-datepicker="{{ $name }}"
      {{ $attributes }}
      />
  </x-forms.wrap>
</div>