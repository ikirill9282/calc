<x-card class="sticky top-3 right-0">
  <form action="" class="flex flex-col justify-center items-stretch gap-4">
    <h2 class="text-2xl">Детали заказа</h2>
    <div class="border-b w-full"></div>
    <div class="flex text-lg">
      <div class="font-bold justify-start items-center">Итого</div>
      <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
      <div class="">0 ₽</div>
    </div>
    <x-button class="w-full">Перейти к оформлению</x-button>
    <x-button class="w-full" outlined>
      <div class="flex justify-center items-center gap-2">
        <span>@include('icons.download')</span>
        <span>Скачать прайс-лист</span>
      </div>
    </x-button>
  </form>
</x-card>