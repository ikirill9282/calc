<x-filament-panels::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">История изменений заявок</h2>

        @if ($logs->isEmpty())
            <div class="rounded-lg bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                Пока нет записей об изменениях.
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="fi-ta-table w-full text-sm text-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">№ заявки</th>
                                <th class="px-6 py-3">Пользователь</th>
                                <th class="px-6 py-3">Поле</th>
                                <th class="px-6 py-3">Старое значение</th>
                                <th class="px-6 py-3">Новое значение</th>
                                <th class="px-6 py-3">Когда</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($logs as $log)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 font-medium text-primary-600">
                                        #{{ $log->order_id }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $log->user?->name ?? 'Система' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ \App\Models\Order::getFieldLabel($log->field) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 break-words">
                                        {{ $log->old_value ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-900 break-words">
                                        {{ $log->new_value ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d.m.Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
