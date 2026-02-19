<div>
  <div class="flex flex-col gap-10">
    @if($this->orders && $this->orders->isNotEmpty())
      @foreach ($this->orders as $order)
        <x-form.fieldset :title="false" set_description="Заказ #{{ $order->id }}" set_class="order-details !pb-14 sm:!pb-14">
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
            
            <div class="basis-1/4 sm:text-right text-xl">
              
              @if($order->individual)
                Расчет индивидуальный
              @else
                <span class="inline-block">Предварительная&nbsp;стоимость:</span>
                <span class="text-xs inline-block my-2">В предварительный расчет не входит стоимость услуги адресного забора груза и погрузочных работ на адресе забора. Такие услуги оплачиваются сверх стоимости доставки на склад маркетплейса.</span>
                <span class="inline-block my-2">{{ \Illuminate\Support\Number::currency($order->total ?? 0, 'RUB', locale: 'ru') }}</span>
              @endif
              
              <p class="text-xs">
                Итоговая стоимость является предварительным расчетом, точная стоимость будет известна после взвешивания и обмера груза на складе приема.
              </p>
            </div>
          </div>

          <div class="order-details-view py-6 mt-6 border-t hidden border-primary-500/50">

            <div class="font-medium text-lg p-4 border mb-4 inline-block text-secondary-400 dark:text-secondary-600 border-secondary-400 dark:border-secondary-600">
              Важно! Дата доставки в РЦ WILDBERRIES может отличаться на 24 часа в ту или иную сторону , но не более чем на 1 сутки с указанной даты.
            </div>


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

            <div class="flex mt-4">
              <x-link 
                href="{{ url('/?reply='.\Illuminate\Support\Facades\Crypt::encrypt($order->id)) }}" 
                class="flex justify-center items-center gap-2 border px-4 py-2 group
                    text-secondary-600 dark:text-secondary-400 border-secondary-600 dark:border-secondary-400
                    hover:bg-secondary-600/15 dark:hover:bg-secondary-400/15
                  ">
                <span class="transition duration-300 group-hover:rotate-180">@include('icons.reload', ['width' => 20, 'height' => 20])</span>
                <span>Повторить заказ</span>
              </x-link>
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
              <div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
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
              <div class="max-w-[85vw] overflow-x-scroll overflow-y-hidden sm:!overflow-hidden">
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
                        {{-- @if(isset($_GET['tt']) && $row['type'] == 'Коробки')
                          @dd(isset($row['volume']), $row)
                        @endif --}}
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
            <div class="border-t border-primary-500/50 mt-6 py-6">
              <div class="text-xl font-bold mb-6">Услуги склада:</div>
              @if (!empty($order->palletizing_type) && !empty($order->palletizing_count))
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
              @endif
            </div>
            <div class="border-t border-primary-500/50 mt-6 py-6">
              <div class="text-xl font-bold mb-6">Комментарий к составу груза:</div>
              <p>{{ $order->cargo_comment }}</p>
            </div>
            <div class="border-t border-primary-500/50 mt-6 py-6">
              <div class="text-xl font-bold mb-6">Контактные данные:</div>
              <div class="flex justify-between items-stretch gap-5 flex-col sm:flex-row">
                <div class="sm:basis-1/2 lg:basis-1/3">
                  <div class="mb-4 font-bold dark:text-primary-300/50 text-primary-600/50">Отправитель:</div>
                  <div class="flex flex-col gap-1">
                    <div class="flex gap-2">
                      <span>Имя:</span>
                      <span>{{ $order->user->name }}</span>
                    </div>
                    <div class="flex gap-2">
                      <span>Email:</span>
                      <span>{{ $order->user->email }}</span>
                    </div>
                    <div class="flex gap-2">
                      <span>Телефон:</span>
                      <span>{{ $order->user->phone }}</span>
                    </div>
                  </div>
                </div>
                <div class="sm:basis-1/2 lg:basis-1/3">
                  <div class="mb-4 font-bold dark:text-primary-300/50 text-primary-600/50">Менеджер:</div>
                  <div class="flex flex-col gap-1">
                    <div class="flex gap-2">
                      <span>Имя:</span>
                      <span>Любимова София</span>
                    </div>
                    <div class="flex gap-2">
                      <span>Email:</span>
                      <span>tk82wb24@gmail.com</span>
                    </div>
                    <div class="flex gap-2">
                      <span>Телефон:</span>
                      <span>+79785550055<br>+79785551920</span>
                    </div>
                  </div>
                </div>
                <div class="sm:basis-1/2 lg:basis-1/3">
                  
                </div>
              </div>
            </div>

            <x-details 
              :order="$order"
              cardClass="!p-0 !m-0 !border-none"
            />
          </div>
          
          <div class="order-details-toggle w-full text-center text-sm py-1 absolute bottom-0 left-0 transition
                    xl:opacity-0 group-hover/card:opacity-100
                    bg-primary-500/25
                    hover:cursor-pointer
                  ">
                <div class="flex items-center justify-center gap-2">
                  <span>Посмотреть детали</span>
                  <span class="icon transition duration-300">@include('icons.arrow-toggle', ['width' => 15, 'height' => 16])</span>
                </div>
          </div>
        </x-form.fieldset>
      @endforeach
    @else
      У вас пока нет заказов.
    @endif
  </div>
</div>
