@props(['set_title', 'set_description'])

<x-card class="relative">
  @if(isset($set_title) || isset($set_descroption))
    <div class="rounded-full flex border-0 absolute top-0 left-16 translate-y-[-50%] shadow bg-primary-100 dark:bg-primary-800">
      <span class="px-4 py-1.5 rounded-full bg-primary-900 text-primary-50 dark:bg-primary-100 dark:text-primary-900">{{ $set_title ?? '' }}</span>
      <span class="px-4 py-1.5">{{ $set_description ?? '' }}</span>
    </div>
  @endif
  {{ $slot }}
</x-card>