@props([
  'show_password' => false,
  'disabled' => false,
])

@php
  $name = $attributes->get('name') ?? '';
  $label = $attributes->get('label') ?? $label ?? '';
  $error_name = preg_replace('/^\w+\.(.*?)$/is', "$1", $name);
@endphp

<div class="bg-inherit w-full group/input">
  <fieldset
    class="input w-full min-h-14 py-2 ps-4 pe-12 ring-0 outline-0 border transition group bg-inherit relative
      border-primary-200 dark:border-primary-400/50 
      placeholder:text-gray-400 @error($error_name) !border-red-500 @enderror
      hover:border-secondary-600 darkhover:border-secondary-400
      focus:border-secondary-600 dark:focus:border-secondary-400
      group-has-focus/input:border-secondary-400 dark:group-has-focus/input:border-secondary-600
      {{ $disabled  ? 'pointer-events-none' : '' }}
      "
    >
    <legend 
      class="absolute top-0 translate-y-[-50%] left-6 bg-inherit px-2 transition group-hover:text-secondary-600 dark:group-hover:text-secondary-400
            text-[11px] sm:text-xs md:text-sm absolute top-0 left-4 px-2 translate-y-[-50%] text-primary-400 transition
            bg-inherit
            {{-- bg-white  --}}
            {{-- dark:bg-primary-900 --}}
            group-hover/input:text-secondary-600 dark:group-hover/input:text-secondary-400
            group-has-focus/input:text-secondary-600 dark:group-has-focus/input:text-secondary-400
            @error($error_name) !text-red-500 @enderror
          "
    >
      {{ $label }}
    </legend>
    {{ $slot }}


    @if(!$show_password)
    <div wire:click.prevent='clearField("{{ $name }}")' class="absolute top-3.5 right-4" data-name="{{ $name }}">
        <div
            class="hover:cursor-pointer transition text-primary-500 hover:text-secondary-600 dark:hover:text-secondary-400">
            @include('icons.close')
        </div>
    </div>
    @else
      <div wire:click.prevent="setShowPassword('{{ $name }}')" class="{{ $attributes->get('showPasswordClass') }} absolute top-3.5 right-4" data-name="{{ $name }}">
          <div
              class="hover:cursor-pointer transition text-primary-500 hover:text-secondary-600 dark:hover:text-secondary-400">
              @include('icons.eye')
          </div>
      </div>
    @endif
  </fieldset>

  @error($error_name)
    <div class="mt-3 text-red-500">
      {{ $message }}
    </div>
  @enderror
</div>