@props([
  'groupClass' => '',
  'label' => '',
])

<div class="group/radio flex justify-start items-center hover:cursor-pointer {{ $groupClass }}">
    <input
      {{ $attributes }}
      type="radio"
      class="peer w-0 {{ $attributes->get('class')}}"
      />
    <label for="{{ $attributes->get('id') }}" class="select-none hover:cursor-pointer flex items-center">
      <div
        class="w-3 min-w-3 h-3 min-h-3 mr-3 transition
          ring-1 ring-offset-3 dark:ring-offset-primary-900
          group-hover/radio:ring-secondary-600 group-hover/radio:dark:ring-secondary-400
          group-has-checked/radio:text-secondary-600 group-has-checked/radio:ring-secondary-600 group-has-checked/radio:dark:ring-secondary-400
        ">
          <div class="w-full h-full scale-0 transition group-has-checked/radio:scale-90 bg-primary-400 group-has-checked/radio:bg-secondary-600 group-has-checked/radio:dark:bg-secondary-400">
          </div>
      </div>
      {{ $label }}
    </label>
</div>
