@props([
  'set_title' => null,
  'set_description' => null, 
  'set_class' => '', 
  'set_loading' => false,
  'title' => true, 
])
<x-card class="relative !p-4 !pt-12 sm:!p-6 sm:!pt-12 md:!p-12 group/card {{ $set_class }}">
  <div class="absolute hidden z-20 top-0 left-0 w-full h-full group-[.disabled]/card:block bg-primary-50/80 dark:bg-primary-900/80">
  </div>
  @if($set_title || $set_description)
    <div class="z-30 flex border-0 absolute top-0 text-nowrap left-4 sm:left-8 md:left-16 translate-y-[-50%] shadow max-w-[90%]
              bg-primary-100 dark:bg-primary-800 
              group-[.disabled]/card:bg-primary-100 group-[.disabled]/card:text-primary-500
              dark:group-[.disabled]/card:bg-primary-800 dark:group-[.disabled]/card:text-primary-500">
      @if($title)
        <span class="px-4 py-1.5 flex justify-center items-center
                bg-primary-900 text-primary-50 dark:bg-primary-100 dark:text-primary-900
                group-[.disabled]/card:bg-primary-200 group-[.disabled]/card:text-primary-500
                dark:group-[.disabled]/card:bg-primary-800 dark:group-[.disabled]/card:text-primary-500 
                dark:group-[.disabled]/card:shadow dark:group-[.disabled]/card:shadow-primary-950/50
              ">
          {{ $set_title ?? '' }}
        </span>
      @endif
      @if ($set_description !== false)
        <span class="px-4 py-1.5 text-wrap">{{ $set_description ?? '' }}</span>
      @endif
    </div>
  @endif

  @if($set_loading)
    <div wire:loading class="absolute top-0 left-0 w-full h-full bg-primary-50/80 dark:bg-primary-900/80 z-20">
      <div class="w-full h-full flex justify-center items-center">
        @include('icons.loading', ['width' => 50, 'height' => 50])
      </div>
    </div>
  @endif
  {{ $slot }}
</x-card>