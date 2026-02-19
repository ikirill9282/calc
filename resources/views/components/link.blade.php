@props([
  'href' => '#', 
  'class' => '',
])
<a 
  {{ $attributes }} 
  href="{{ $href }}" 
  class="transition text-primary-900 hover:text-secondary-600
         dark:text-primary-50 dark:hover:text-secondary-400
         {{ $class }}
         "
>
  {{ $slot }}
</a>