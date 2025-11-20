<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{-- Сводка по выбранным заявкам --}}
        @php
            $selectedIds = $this->selectedTableRecords ?? [];
            $selectedCount = is_array($selectedIds) ? count($selectedIds) : 0;
        @endphp
        
        {{-- Временный отладочный вывод --}}
        @if ($selectedCount > 0)
            <div class="mt-4 p-4 bg-yellow-100 dark:bg-yellow-900 text-sm">
                Отладка: Выбрано {{ $selectedCount }} записей. IDs: {{ implode(', ', array_slice($selectedIds, 0, 5)) }}...
            </div>
        @endif
        
        @if ($selectedCount >= 2)
            @php
                $summary = $this->getSelectedOrdersSummary();
            @endphp
            @if ($summary)
                <div wire:key="selected-summary-{{ $selectedCount }}-{{ md5(implode(',', $selectedIds)) }}" class="mt-6">
                    @include('filament.tables.selected-summary', ['summary' => $summary])
                </div>
            @else
                <div class="mt-4 p-4 bg-red-100 dark:bg-red-900 text-sm">
                    Отладка: Сводка не создана (summary = null)
                </div>
            @endif
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

