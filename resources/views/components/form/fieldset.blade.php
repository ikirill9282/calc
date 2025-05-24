@props(['set_title', 'set_description', 'title' => true])
<x-card class="relative p-6 py-12 2xl:p-12">
  @if(isset($set_title) || isset($set_description))
    <div class="rounded-full flex border-0 absolute top-0 text-nowrap left-4 sm:left-8 md:left-16 translate-y-[-50%] shadow bg-primary-100 dark:bg-primary-800">
      @if($title)
        <span class="px-4 py-1.5 rounded-full bg-primary-900 text-primary-50 dark:bg-primary-100 dark:text-primary-900">{{ $set_title ?? '' }}</span>
      @endif
      <span class="px-4 py-1.5">{{ $set_description ?? '' }}</span>
    </div>
  @endif
  {{ $slot }}
</x-card>