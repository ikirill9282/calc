@props([
  'count' => 0,
])

<div class="counter">
  <input type="hidden" {{ $attributes }} data-count="{{ $count }}">
  <div class="flex justify-between items-center bg-primary-100 dark:bg-primary-800">
    <span class="minus p-2 sm:p-3 select-none hover:cursor-pointer transition hover:text-secondary-600 dark:hover:text-secondary-400
                ">
      @include('icons.minus', ['width' => 15, 'height' => 15])
    </span>
    {{-- @dump($count) --}}
    <span class="count px-2 sm:px-4 select-none min-w-14 text-center">{{ intval($count) }}</span>
    <span class="plus p-2 sm:p-3 select-none hover:cursor-pointer transition hover:text-secondary-600 
               dark:hover:text-secondary-400  
                ">
      @include('icons.plus', ['width' => 15, 'height' => 15])
    </span>
  </div>
</div>