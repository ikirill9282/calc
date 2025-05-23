@php
    $menu = [
        [
            'label' => 'Главная',
            'route' => route('home'),
            'icon' => 'home',
        ],
        [
            'label' => 'История заказов',
            'route' => route('history'),
            'icon' => 'history',
        ],
        [
            'label' => 'Контрагенты',
            'route' => route('agents'),
            'icon' => 'truck',
        ],
    ];
@endphp

<aside class="p-3 h-full">
    <div class="flex flex-col p-3 h-full w-full rounded-lg transition shadow bg-white dark:bg-primary-800">
        <div class="flex justify-center items-center w-full mb-3 py-3">
            <div class="text-2xl uppercase flex justify-between items-center gap-2">
                <span class="bg-clip-content">@include('icons.globe', ['width' => 35, 'height' => 35])</span>
                <span>{{ env('APP_NAME', '') }}</span>
            </div>
        </div>
        <nav class="mb-3">
            <ul class="flex flex-col gap-2">
                @foreach ($menu as $item)
                    <li class="">
                        <a 
                          href="{{ $item['route'] }}"
                          class="flex group justify-start items-center gap-2 w-full p-2 rounded-lg transition
                                bg-primary-200/25 hover:bg-primary-200/75
                                dark:bg-primary-700/25 dark:hover:bg-primary-700
                                "
                          >
                            <span class="p-2 rounded-lg transition bg-primary-200/25 group-hover:bg-secondary-600 group-hover:text-white ">@include("icons.{$item['icon']}", ['width' => 20, 'height' => 20])</span>
                            <span class="">{{ $item['label'] }}</span>
                          </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <x-button>
          Войти в личный кабинет
        </x-button>

        <div class="mt-auto">
          <label for="theme-button" class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="theme-button" class="sr-only peer" />
            <div class="w-14 h-6 bg-primary-600 rounded-full peer-checked:bg-primary-100 transition-colors duration-300"></div>
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow-md flex items-center justify-center peer-checked:text-primary-100 peer-checked:translate-x-8 peer-checked:bg-primary-800 transition-all duration-300">
              <svg class="w-4 h-4 peer-checked:hidden" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 4.5a1 1 0 100 2 1 1 0 000-2zm0 11a4 4 0 110-8 4 4 0 010 8zm6.364-7.364a1 1 0 10-1.414 1.414 1 1 0 001.414-1.414zm-12.728 0a1 1 0 00-1.414 1.414 1 1 0 001.414-1.414zm12.728 7.728a1 1 0 10-1.414-1.414 1 1 0 001.414 1.414zm-12.728 0a1 1 0 00-1.414-1.414 1 1 0 001.414 1.414zM12 19.5a1 1 0 100 2 1 1 0 000-2zm7.5-7.5a1 1 0 100 2 1 1 0 000-2z"/>
              </svg>
              <svg class="w-4 h-4 hidden peer-checked:block" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.79 9.79z"/>
              </svg>
            </div>
          </label>
        </div>
    </div>
</aside>
