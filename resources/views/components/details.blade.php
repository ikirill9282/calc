@props([
  'order',
  'formClass' => '',
  'cardClass' => '',
  'wrapClass' => '',
])

<div class="sticky top-3 right-0 {{ $wrapClass }}">
  <x-card class="mb-4 {{ $cardClass }}">
    <form action="" class="flex flex-col justify-center items-stretch gap-4 {{ $formClass }}">
      <h2 class="text-2xl">Предварительная стоимость:</h2>
      <div class="border-b w-full"></div>
      @if($order->transfer_method == 'pick')
        <div class="flex opacity-50">
          <div class="justify-start items-center">Забор груза</div>
          <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
          @if($order->pick == 0)
            По согласованию
          @else
            <div class="">{{ Illuminate\Support\Number::currency($order->pick, 'RUB', locale: 'ru')}}</div>
          @endif
        </div>
      @endif
      @if(!empty($order->delivery))
        <div class="flex opacity-50">
          <div class="justify-start items-center">Доставка груза</div>
          <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
          <div class="">{{ Illuminate\Support\Number::currency($order->delivery, 'RUB', locale: 'ru') }}</div>
        </div>
      @endif
      @if(!empty($order->additional))
        <div class="flex opacity-50">
          <div class="justify-start items-center">Складские услуги</div>
          <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
          <div class="">{{ Illuminate\Support\Number::currency($order->additional, 'RUB', locale: 'ru') }}</div>
        </div>
      @endif
      <div class="flex text-lg">
        <div class="font-bold justify-start items-center">Итого</div>
        <div class="border-b border-dotted grow mx-1 translate-y-[-20%]"></div>
        <div class="">{{ Illuminate\Support\Number::currency($order->total, 'RUB', locale: 'ru') }}</div>
      </div>
      <p class="text-xs">
        Итоговая стоимость является предварительным расчетом, точная стоимость будет известна после взвешивания и обмера груза на складе приема.
      </p>
      {{ $slot }}
    </form>
  </x-card>
  {{ $bot ?? '' }}
</div>