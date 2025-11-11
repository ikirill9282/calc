<x-filament::page>
    <x-filament::tabs>
        <x-filament::tabs.item :active="true" tag="button">
            Склад — наличными (забор нет)
        </x-filament::tabs.item>
        <x-filament::tabs.item tag="button">
            Заборы — наличными (забор да)
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="mt-6 space-y-10">
        <x-filament::section>
            <x-slot name="heading">Склад — наличными, забор груза отсутствует</x-slot>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">№ заявки</th>
                            <th class="px-4 py-2 text-left font-semibold">Отправитель</th>
                            <th class="px-4 py-2 text-left font-semibold">Дата поставки на РЦ</th>
                            <th class="px-4 py-2 text-left font-semibold">РЦ и адрес</th>
                            <th class="px-4 py-2 text-left font-semibold">Доставка</th>
                            <th class="px-4 py-2 text-left font-semibold">Палетирование</th>
                            <th class="px-4 py-2 text-left font-semibold">Оплата за забор</th>
                            <th class="px-4 py-2 text-left font-semibold">Принято</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($this->warehouseCash as $order)
                            <tr>
                                <td class="px-4 py-2">{{ $order->id }}</td>
                                <td class="px-4 py-2">{{ $order->agent->title ?? '—' }}</td>
                                <td class="px-4 py-2">{{ optional($order->delivery_date)->format('d.m.Y') }}</td>
                                <td class="px-4 py-2">{{ $order->distribution_label ?? '—' }}</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->delivery ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->additional ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->pick ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->total ?? 0), 2, ',', ' ') }} руб.</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-500">Данных нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Заборы — наличными, забор груза оформлен</x-slot>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">№ заявки</th>
                            <th class="px-4 py-2 text-left font-semibold">Отправитель</th>
                            <th class="px-4 py-2 text-left font-semibold">Дата поставки на РЦ</th>
                            <th class="px-4 py-2 text-left font-semibold">РЦ и адрес</th>
                            <th class="px-4 py-2 text-left font-semibold">Доставка</th>
                            <th class="px-4 py-2 text-left font-semibold">Палетирование</th>
                            <th class="px-4 py-2 text-left font-semibold">Оплата за забор</th>
                            <th class="px-4 py-2 text-left font-semibold">Принято</th>
                            <th class="px-4 py-2 text-left font-semibold">ФИО водителя</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($this->pickupCash as $order)
                            <tr>
                                <td class="px-4 py-2">{{ $order->id }}</td>
                                <td class="px-4 py-2">{{ $order->agent->title ?? '—' }}</td>
                                <td class="px-4 py-2">{{ optional($order->delivery_date)->format('d.m.Y') }}</td>
                                <td class="px-4 py-2">{{ $order->distribution_label ?? '—' }}</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->delivery ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->additional ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->pick ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->total ?? 0), 2, ',', ' ') }} руб.</td>
                                <td class="px-4 py-2">{{ $order->driver_name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-4 text-center text-gray-500">Данных нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament::page>
