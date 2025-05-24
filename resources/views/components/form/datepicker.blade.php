<div class="relative {{ $pickerClass ?? '' }}">
  <div class="absolute top-[50%] left-6 translate-y-[-50%] dark:text-primary-500">
    @include('icons.calendar')
  </div>
  <x-form.input
    inputId="{{ $inputId ?? '' }}"
    name="{{ $name ?? '' }}" 
    label="{{ $label ?? '' }}" 
    class="pl-16 datepicker"
    labelClass="{{ $labelClass ?? '' }}"
    :attrs="['autocomplete' => 'off', 'aria-autocomplete' => 'off']"
    />
</div>