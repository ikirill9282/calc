<div>
  <div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10">
    <div class="">
      <x-card class="!p-0">
        {{-- @dump($order) --}}
        <div class="bg-primary-400/25 p-6 border-b border-primary-500/15">
          <p class="text-2xl font-medium">Ваш заказ №{{ $order->id }} успешно создан</p>
          <p class="font-thin">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d.m.Y') }}</p>
        </div>
        <div class="flex justify-start items-center gap-6 p-6 border-b border-primary-500/15">
          <div class="text-secondary-600 dark:text-secondary-400">
            @include('icons.check', ['width' => 50, 'height' => 50])
          </div>
          <div class="">
            <p>Ваш заказ успешно оформлен! В ближайшее время с вами свяжется наш менеджер для уточнения деталей.</p>
            <p>На вашу почту уже отправлено письмо с информацией о заказе.</p>
            <p>Спасибо, что выбрали нашу компанию!</p>
          </div>
        </div>
        <div class="flex flex-col gap-10 p-6">
          <div class="flex flex-col gap-1.5">
            <div class="font-bold text-lg">Способ оплаты:</div>
            <div class="">{{ $order->getPaymentMethodLabel() }}</div>
          </div>
          <div class="flex flex-col gap-1.5">
            <div class="font-bold text-lg">Отправитель:</div>
            <div class="flex flex-col w-full gap-2">
              <div class="flex">
                <div class="">ФИО:</div>
                <div class="px-2">{{ $order->user->name }}</div>
              </div>
              <div class="flex">
                <div class="">Email:</div>
                <div class="px-2">{{ $order->user->email }}</div>
              </div>
              <div class="flex">
                <div class="">Телефон:</div>
                <div class="px-2">{{ $order->user?->phone }}</div>
              </div>
            </div>
          </div>
        </div>
      </x-card>
    </div>
    <div class="">
      <x-details :order="$order">
        <x-slot:bot>
          <x-card>
            <div class="flex flex-col gap-4 mb-4">
              <h2 class="text-2xl">Ваш менеджер:</h2>
              <div class="border-b w-full"></div>
            </div>
            <div class="flex flex-col gap-4">
              <div class="flex flex-col gap-1">
                <div class="font-bold">Имя:</div>
                <div class="">Иванов Иван Иванович</div>
              </div>
              <div class="flex flex-col gap-1">
                <div class="font-bold">Email:</div>
                <div class="">1234569@gmail.com</div>
              </div>
              <div class="flex flex-col gap-1">
                <div class="font-bold">Телефон:</div>
                <div class="">+71234569192</div>
              </div>
            </div>
          </x-card>
        </x-slot:bot>
      </x-details>
    </div>
  </div>
</div>
