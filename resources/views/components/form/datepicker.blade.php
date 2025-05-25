<div 
  class="datepicker-group relative {{ $pickerClass ?? '' }}"
  data-name="{{ $inputName ?? '' }}"
  >
  <div class="absolute top-[50%] left-6 translate-y-[-50%] dark:text-primary-500">
    @include('icons.calendar')
  </div>
  <x-form.input
    inputId="{{ $inputId ?? '' }}"
    inputName="{{ $inputName ?? '' }}" 
    id="{{ $id ?? '' }}"
    label="{{ $label ?? '' }}"
    text="{{ $text ?? '' }}"
    class="pl-16 datepicker"
    labelClass="{{ $labelClass ?? '' }}"
    :attrs="['autocomplete' => 'off', 'aria-autocomplete' => 'off', 'data-datepicker' => ($inputName ?? '')]"
    />
</div>