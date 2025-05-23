@props(['href'])
<a href="{{ $href ?? '#' }}" class="transition text-primary-900 dark:text-primary-50 hover:text-secondary-600 dark:hover:text-secondary-400">
  {{ $slot }}
</a>