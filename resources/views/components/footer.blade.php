<footer class="px-10 pb-3">
    <div class="px-12 w-full rounded-lg shadow bg-white dark:bg-primary-800">
        <div class="grid grid-cols-3 gap-12 py-12">
            <div class="flex flex-col gap-4">
                <span>E-mail:<x-link href="mailto:test@mail.ru">test@mail.ru</x-link></span>
                <span>Адрес: г. Иваново, ул. Смирнова д 4, 2 этаж</span>
            </div>
            <div class="">
                <nav>
                    <ul class="flex flex-col justify-start items-stretch gap-4">
                        <li><x-link>Главная</x-link></li>
                        <li><x-link>История заказов</x-link></li>
                        <li><x-link>Контрагенты</x-link></li>
                    </ul>
                </nav>
            </div>
            <div class="flex flex-col gap-4">
                <p>Мы в соц. сетях:</p>
                <div class="flex gap-4">
                    <x-link>@include('icons.vk')</x-link>
                    <x-link>@include('icons.tg')</x-link>
                </div>
            </div>
        </div>
        <div class="border-b w-full border-primary-600/25"></div>
        <div class="grid grid-cols-3 gap-12 py-6">
            <div class="">
              {{ env('APP_NAME', '') }} © {{ date('Y', time()) }}
            </div>
            <div class=""></div>
            <div class="">
              {{-- <x-link>Сделано в {{ env('APP_NAME', '') }}</x-link> --}}
            </div>
        </div>
    </div>
</footer>
