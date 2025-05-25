<header class="py-3 sticky top-0 z-90 lg:static bg-primary-50 dark:bg-primary-950">
  <div class="flex justify-between items-center px-5 lx:px-10 2xl:px-18 lg:hidden">
    <div id="burger" class="">
      @include('icons.burger')
    </div>
    <div class="relative flex justify-center items-center gap-2">
      <span class="">@include('icons.globe')</span>
      <span class="uppercase text-2xl">{{ config('app.name') }}</span>
    </div>
    <div class="p-2 rounded-full bg-primary-100 dark:bg-primary-800">
      @include('icons.profile', ['width' => 20, 'height' => 20])
    </div>
  </div>
</header>