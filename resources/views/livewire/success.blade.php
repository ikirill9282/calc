<div>
  <div class="grid grid-cols-[1fr] xl:grid-cols-[1fr_350px] 2xl:grid-cols-[1fr_400px] gap-5 2xl:gap-10">
    <div class="">
      <x-card class="!p-0">
        <div class="bg-primary-400/25 p-6 border-b border-primary-500/15">
          <p class="text-2xl font-medium">Ваш заказ №{{ $order->id }} успешно создан</p>
          <p class="font-thin">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d.m.Y') }}</p>
        </div>
        <div class="flex justify-start items-center gap-6 p-6 border-b border-primary-500/15">
          <div class="text-secondary-600 dark:text-secondary-400">
            @include('icons.check', ['width' => 50, 'height' => 50])
          </div>
          <div class="">
            <p>Ваш заказ успешно оформлен и принят! Спасибо, что выбрали нашу компанию!</p>
            {{-- <p>На вашу почту уже отправлено письмо с информацией о заказе.</p> --}}
          </div>
        </div>
        <div class="px-3 sm:px-6 md:px-12 pt-6 pb-6 border-b border-primary-500/15">
          <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <div class="text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
                @include('icons.info', ['width' => 24, 'height' => 24])
              </div>
              <p class="text-amber-900 dark:text-amber-100 font-medium">
                <strong>Важно:</strong> Данные водителя отправляются на указанную в контрагенте электронную почту в день выгрузки.
              </p>
            </div>
          </div>
        </div>
        <div class="px-3 sm:px-6 md:px-12 pt-6">
          <div class="flex flex-col !gap-3">
            <p>
              Уважаемые клиенты, просим Вас внимательно отнестись к наличию упаковочных листов на каждом коробе отгрузки. Упаковочный лист необходим для оперативной идентификации коробов на складе.
            </p>
            <p>Упаковочный лист должен содержать информацию о:</p>
            <ul class="">
              <li>- количестве коробов в поставке</li>
              <li>- склад (РЦ маркетплейса) назначения</li>
              <li>- наименование поставщика/отправителя (ваше ИП или ООО)</li>
              <li>- дата отправления (выезд со склада)</li>
              <li>- дата выгрузки на складе (РЦ маркетплейса) назначения</li>
            </ul>
            <p>Просим клеить упаковочный лист на торце коробов.</p>
          </div>
        </div>
        <x-form.fieldset :title="false" :set_description="false" set_class="order-details !pb-14 sm:!pb-14 !border-none">
          <div class="flex justify-start items-start sm:items-stretch gap-2 flex-col-reverse sm:flex-row">
            <div class="basis-3/4">
              <div class="flex flex-col gap-1 text-2xl font-medium mb-4">
                <p class=""> 
                  <span class="float-left translate-y-1 mr-2 leading-0">@include('icons.box')</span>
                  <span>Заказ на доставку {{ $order->getCity() }} - {{ $order->distributor_center_id }}</span>
                </p>
                <span class="text-sm text-primary-600/50 dark:text-primary-200/50">от: {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d.m.Y') }}</span>
              </div>
              @if($order->transfer_method === 'pick')
                <div class="mb-1">Заберем груз от вас: {{ \Illuminate\Support\Carbon::parse($order->transfer_method_pick_date)->format('d.m.Y') }}</div>
              @endif
              <div class="mb-1">Отправка со склада: {{ \Illuminate\Support\Carbon::parse($order->post_date)->format('d.m.Y') }}</div>
              <div class="">Прибытие в РЦ {{ $order->distributor_id }}: {{ \Illuminate\Support\Carbon::parse($order->delivery_date)->format('d.m.Y') }}</div>
            </div>
          </div>


          <div class="font-medium text-lg p-4 border mt-4 inline-block text-secondary-400 dark:text-secondary-600 border-secondary-400 dark:border-secondary-600">
            Важно! Дата доставки в РЦ WILDBERRIES может отличаться на 24 часа в ту или иную сторону , но не более чем на 1 сутки с указанной даты.
          </div>

          <div class="py-6 mt-6 border-t border-primary-500/50">
            <div class="flex justify-start items-start gap-2 mb-4">
              <p class="flex justify-start items-center gap-2 min-w-26">
                <span>@include('icons.point')</span>
                <span>Откуда:</span>
              </p>
              <p class="">{{ $order->warehouse_id }}</p>
            </div>
            <div class="flex justify-start items-start gap-2">
              <p class="flex justify-start items-center gap-2 min-w-26">
                <span>@include('icons.flag')</span>
                <span>Куда:</span>
              </p>
              <p class="">{{ $order->distributor_id }} {{ $order->distributor_center_id }}</p>
            </div>
            
            <div class="border-t border-primary-500/50 mt-6 py-6">
              <div class="text-xl font-bold mb-6">Способ передачи груза:</div>
              <div class="flex justify-start items-stretch gap-5 flex-col sm:flex-row sm:gap-10 lg:gap-20">
                <p class="font-medium text-secondary-600 dark:text-secondary-400">
                  {{ 
                    match($order->transfer_method) {
                      'receive' => 'Ожидаем груз на складе',
                      'pick' => 'Заберем груз с вашего склада',
                    }
                  }}
                </p>
                <p class="flex flex-col">
                  <span class="dark:text-primary-300/50 text-primary-600/50">Когда:</span>
                  <span>
                    {{ 
                      match($order->transfer_method) {
                        'receive' => \Illuminate\Support\Carbon::parse($order->transfer_method_receive_date)->format('d.m.Y'),
                        'pick' => \Illuminate\Support\Carbon::parse($order->transfer_method_pick_date)->format('d.m.Y'),
                      }
                    }}
                  </span>
                  @if($order->transfer_method == 'pick')
                    <span>к вам подъедет машина за грузом</span>
                  @endif
                </p>
                <p class="flex flex-col">
                  <span class="dark:text-primary-200/50 text-primary-600/50">Адрес:</span>
                  <span>
                    {{ 
                      match($order->transfer_method) {
                        'receive' => $order->warehouse_id,
                        'pick' => $order->transfer_method_pick_address,
                      }
                    }}
                  </span>
                </p>
              </div>
            </div>
            <div class="border-t border-primary-500/50 mt-6 py-6">
              <div class="text-xl font-bold mb-6">Состав груза:</div>
              @php
                $table_data = [];
                if ($order->cargo == 'boxes') {
                  $table_data[] = [
                    'type' => 'Коробки',
                    'count' => $order->boxes_count,
                    'volume' => $order->boxes_volume,
                    'cargo' => $order->cargo_type,
                  ];
                } elseif ($order->cargo == 'pallets') {
                  $table_data[] = [
                    'type' => 'Паллеты',
                    'count' => $order->pallets_count,
                    'volume' => $order->pallets_volume,
                    'cargo' => $order->cargo_type,
                  ];
                }
              @endphp

              <div class="max-w-[85vw] overflow-x-scroll overflow-y-hidden">
                <table>
                  <thead>
                    <tr>
                      <th class="text-nowrap py-2 px-4 font-normal dark:text-primary-200/50 text-primary-600/50">Тип доставки:</th>
                      <th class="text-nowrap py-2 px-4 font-normal dark:text-primary-200/50 text-primary-600/50">Кол-во:</th>
                      {{-- <th cltext-nowrap ass="py-2 px-4 font-normal dark:text-primary-200/50 text-primary-600/50">Вес:</th> --}}
                      <th class="text-nowrap py-2 px-4 font-normal dark:text-primary-200/50 text-primary-600/50">Объем м3:</th>
                      <th class="text-nowrap py-2 px-4 font-normal dark:text-primary-200/50 text-primary-600/50">Тип груза:</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($table_data as $row)
                      <tr>
                        <td class="py-2 px-4">{{ $row['type'] ?? '' }}</td>
                        <td class="py-2 px-4">@if(isset($row['count'])) {{ $row['count'] }} шт @endif</td>
                        {{-- <td class="py-2 px-4">@if(isset($row['weight'])) {{ $row['weight'] }} кг @endif</td> --}}
                        <td class="py-2 px-4">@if(isset($row['volume'])) {{ (float)$row['volume'] }} м3 @endif</td>
                        <td class="py-2 px-4">@if(isset($row['cargo'])) {{ $row['cargo'] }} @endif</td>
                      </tr>                      
                    @endforeach
                  </tbody>
                </table>
              </div>
              
            </div>


            @if (!empty($order->palletizing_type) && !empty($order->palletizing_count))
              <div class="border-t border-primary-500/50 mt-6 py-6">
                <div class="text-xl font-bold mb-6">Услуги склада:</div>
                <div class="grid grid-cols-[130px_1fr] grid-rows-2">
                  <p class="col-span-1 row-span-1 font-normal dark:text-primary-200/50 text-primary-600/50">
                    Наименование:
                  </p>
                  <p class="col-span-1 row-span-1">{{ match($order->palletizing_type) {
                    'single' => 'Палетирование',
                    'pallet' => 'Поддон и палетирование',
                  } }}</p>
                  <p class="col-span-1 row-span-2 font-normal dark:text-primary-200/50 text-primary-600/50">
                    Количество:
                  </p>
                  <p class="col-span-1 row-span-2">{{ $order->palletizing_count }}шт.</p>
                </div>
              </div>
            @endif


            @if(!empty($order->cargo_comment))
              <div class="border-t border-primary-500/50 mt-6 py-6">
                <div class="text-xl font-bold mb-6">Комментарий к составу груза:</div>
                <p>{{ $order->cargo_comment }}</p>
              </div>
            @endif
          </div>
        </x-form.fieldset>
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
                <div class="">Любимова София</div>
              </div>
              <div class="flex flex-col gap-1">
                <div class="font-bold">Email:</div>
                <div class="">tk82wb24@gmail.com</div>
              </div>
              <div class="flex flex-col gap-1">
                <div class="font-bold">Телефон:</div>
                <div class="">+79785550055<br>+79785551920</div>
              </div>
            </div>
          </x-card>
        </x-slot:bot>
      </x-details>
    </div>
  </div>
</div>
