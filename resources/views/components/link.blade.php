@props(['href'])
<a href="{{ $href ?? '#' }}" class="transition text-primary-900 dark:text-primary-50 hover:text-secondary-600">
  {{ $slot }}
</a>