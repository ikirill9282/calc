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
                            <th class="px-4 py-2 text-left font-semibold">Дата создания</th>
                            <th class="px-4 py-2 text-left font-semibold">Контрагент</th>
                            <th class="px-4 py-2 text-left font-semibold">Склад</th>
                            <th class="px-4 py-2 text-left font-semibold">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($this->warehouseCash as $order)
                            <tr>
                                <td class="px-4 py-2">{{ $order->id }}</td>
                                <td class="px-4 py-2">{{ optional($order->created_at)->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-2">{{ $order->agent->title ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $order->warehouse_id ?? '—' }}</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->total ?? 0), 2, ',', ' ') }} руб.</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">Данных нет.</td>
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
                            <th class="px-4 py-2 text-left font-semibold">Дата создания</th>
                            <th class="px-4 py-2 text-left font-semibold">Адрес забора</th>
                            <th class="px-4 py-2 text-left font-semibold">Контрагент</th>
                            <th class="px-4 py-2 text-left font-semibold">Оплата забора</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($this->pickupCash as $order)
                            <tr>
                                <td class="px-4 py-2">{{ $order->id }}</td>
                                <td class="px-4 py-2">{{ optional($order->created_at)->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-2">{{ $order->transfer_method_pick_address ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $order->agent->title ?? '—' }}</td>
                                <td class="px-4 py-2 font-semibold">{{ number_format((float) ($order->pick ?? 0), 2, ',', ' ') }} руб.</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">Данных нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament::page>
