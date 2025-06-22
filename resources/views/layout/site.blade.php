@php
  $darkMode = (\Illuminate\Support\Facades\Session::has('darkMode') && \Illuminate\Support\Facades\Session::get('darkMode') ? 'dark' : '');   
@endphp
<!DOCTYPE html>
<html lang="en" class="{{ $darkMode }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Расчет заказа')</title>

  {{-- <link rel="icon" href="{{ asset('/favicon.svg') }}" type="image/svg+xml"> --}}
  <link rel="icon" type="image/png" href="{{ asset('/favicon.png') }}">

  @livewireStyles

  @vite(['resources/css/app.css'])

  @stack('css')
</head>
<body class="transition grid grid-cols-[1fr] lg:grid-cols-[300px_1fr] text-primary-900 bg-primary-50 dark:bg-primary-950 dark:text-primary-50">
  
  <div class="fixed z-90 w-full h-full transition duration-300
              bg-black/75 lg:bg-transparent
              lg:col-span-1 translate-x-[-100%] lg:translate-x-0 lg:sticky lg:top-0 lg:left-0 lg:h-screen"
      id="menu"
    >
    <div class="max-w-[300px] h-full lg:w-full">
      <x-sidebar></x-sidebar>
    </div>
  </div>
  <div class="col-span-1">
    <x-header></x-header>

    <main class="">
      @yield('content')
      
      @livewire('modal')
    </main>

    <x-footer></x-footer>
  </div>


  @vite(['resources/js/app.js'])

  @livewireScripts

  @stack('js')
  
</body>
</html>