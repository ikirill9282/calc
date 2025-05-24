@php
  $darkMode = (\Illuminate\Support\Facades\Session::has('darkMode') && \Illuminate\Support\Facades\Session::get('darkMode') ? 'dark' : '');   
@endphp
<!DOCTYPE html>
<html lang="en" class="{{ $darkMode }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  @vite(['resources/css/app.css'])

  @stack('css')
</head>
<body class="transition  grid grid-cols-[300px_1fr] text-primary-900 bg-primary-50 dark:bg-primary-950 dark:text-primary-50">
  
  <div class="col-span-1 h-screen sticky top-0 left-0">
    <x-sidebar></x-sidebar>
  </div>
  <div class="col-span-1">
    <x-header></x-header>

    <main class="min-h-screen">
      @yield('content')
    </main>

    <x-footer></x-footer>
  </div>

  @vite(['resources/js/app.js'])

  @stack('js')
  
</body>
</html>