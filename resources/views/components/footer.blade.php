<footer class="px-2 sm:px-5 2xl:px-10 pb-3">
    <div class="px-6 2xlpx-12 w-full rounded-lg shadow bg-white dark:bg-primary-900">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-12 py-6 lg:py-12">
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
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-12 py-6">
            <div class="">
              {{ env('APP_NAME', '') }} © {{ date('Y', time()) }}
            </div>
            <div class="hidden"></div>
            <div class="hidden">
              <x-link>Сделано в {{ env('APP_NAME', '') }}</x-link>
            </div>
        </div>
    </div>
</footer>
