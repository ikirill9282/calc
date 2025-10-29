<x-filament-panels::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">История изменений заявок</h2>

        @if ($logs->isEmpty())
            <div class="rounded-lg bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                Пока нет записей об изменениях.
            </div>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">№ заявки</th>
                            <th class="px-4 py-3">Пользователь</th>
                            <th class="px-4 py-3">Поле</th>
                            <th class="px-4 py-3">Старое значение</th>
                            <th class="px-4 py-3">Новое значение</th>
                            <th class="px-4 py-3">Когда</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 font-medium text-primary-600">
                                    #{{ $log->order_id }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $log->user?->name ?? 'Система' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $log->field }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $log->old_value ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-900">
                                    {{ $log->new_value ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $log->created_at->format('d.m.Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
