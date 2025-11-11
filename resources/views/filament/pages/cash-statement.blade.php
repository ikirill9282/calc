<x-filament::page>
    <div x-data="{ tab: 'warehouse' }" class="space-y-6">
        <div class="flex items-center gap-4">
            <button
                type="button"
                class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
                :class="tab === 'warehouse' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white dark:bg-white/10 border-gray-300 dark:border-white/10 text-gray-700 dark:text-white'"
                x-on:click="tab = 'warehouse'"
            >
                Склад — наличными (забор нет)
            </button>
            <button
                type="button"
                class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
                :class="tab === 'pickup' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white dark:bg-white/10 border-gray-300 dark:border-white/10 text-gray-700 dark:text-white'"
                x-on:click="tab = 'pickup'"
            >
                Заборы — наличными (забор да)
            </button>
        </div>

        <div x-show="tab === 'warehouse'" x-cloak>
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
        </div>

        <div x-show="tab === 'pickup'" x-cloak>
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
    </div>
</x-filament::page>
