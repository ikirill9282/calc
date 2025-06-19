@props([
  'amount' => 0,
  'additional' => 0,
  'delivery' => 0,
  'pickamount' => 0,
])

<div class="sticky top-3 right-0">
  <x-card class="mb-4">
    <form action="" class="flex flex-col justify-center items-stretch gap-4">
      <h2 class="text-2xl">Детали заказа</h2>
      <div class="border-b w-full"></div>
      @if(!empty($pickamount))
        <div class="flex opacity-50">
          <div class="justify-start items-center">Забор груза</div>
          <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
          <div class="">{{ $pickamount }} ₽</div>
        </div>
      @endif
      @if(!empty($delivery))
        <div class="flex opacity-50">
          <div class="justify-start items-center">Доставка груза</div>
          <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
          <div class="">{{ $delivery }} ₽</div>
        </div>
      @endif
      @if(!empty($additional))
        <div class="flex opacity-50">
          <div class="justify-start items-center">Складские услуги</div>
          <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
          <div class="">{{ $additional }} ₽</div>
        </div>
      @endif
      <div class="flex text-lg">
        <div class="font-bold justify-start items-center">Итого</div>
        <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
        <div class="">{{ $amount }} ₽</div>
      </div>
      <x-button 
          wire:click.prevent="submit" 
          class="w-full 
                {{ auth()->check() ? '' : 'open_auth' }} 
                {{ $this->isFieldDisabled(7) ? 'pointer-events-none select-none !bg-primary-500' : '' }}
                "
              >Перейти к оформлению</x-button>
      <x-button class="w-full" outlined>
        <div class="flex justify-center items-center gap-2">
          <span>@include('icons.download')</span>
          <span>Скачать прайс-лист</span>
        </div>
      </x-button>
    </form>
  </x-card>
  {{-- <x-card>
    <x-link wire:click.prevent="showManager" class="block text-center">Связаться с менеджером</x-link>
  </x-card> --}}
</div>