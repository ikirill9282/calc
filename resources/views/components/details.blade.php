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
      <div class="text-xs">В предварительный расчет не входит стоимость услуги адресного забора груза и погрузочных работ на адресе забора. Такие услуги оплачиваются сверх стоимости доставки на склад маркетплейса.</div>
      @if($order->individual)
        <p class="text-lg">Расчет индивидуальный</p>
      @else
        <div class="border-b w-full mb-2"></div>
        @if(!empty($order->delivery))
          <div class="flex justify-between items-center gap-4 py-1 opacity-50">
            <div class="flex-shrink-0">Доставка груза</div>
            <div class="border-b border-dotted grow min-w-[20px] translate-y-[-20%]"></div>
            <div class="flex-shrink-0 whitespace-nowrap">{{ Illuminate\Support\Number::currency($order->delivery, 'RUB', locale: 'ru') }}</div>
          </div>
        @endif
        @if(!empty($order->additional))
          <div class="flex justify-between items-center gap-4 py-1 opacity-50">
            <div class="flex-shrink-0">Складские услуги</div>
            <div class="border-b border-dotted grow min-w-[20px] translate-y-[-20%]"></div>
            <div class="flex-shrink-0 whitespace-nowrap">{{ Illuminate\Support\Number::currency($order->additional, 'RUB', locale: 'ru') }}</div>
          </div>
        @endif
        <div class="flex justify-between items-center gap-4 py-2 text-lg mt-2 border-t pt-2">
          <div class="font-bold flex-shrink-0">Итого</div>
          <div class="border-b border-dotted grow min-w-[20px] translate-y-[-20%]"></div>
          <div class="font-bold flex-shrink-0 whitespace-nowrap">{{ Illuminate\Support\Number::currency($order->total, 'RUB', locale: 'ru') }}</div>
        </div>
        <p class="text-xs">
          Итоговая стоимость является предварительным расчетом, точная стоимость будет известна после взвешивания и обмера груза на складе приема.
        </p>
      @endif
      {{ $slot }}
    </form>
  </x-card>
  {{ $bot ?? '' }}
</div>