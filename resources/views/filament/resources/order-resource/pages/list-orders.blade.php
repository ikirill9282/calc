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

        {{-- ТЕСТ: Проверка что view используется --}}
        <div class="mt-4 p-2 bg-blue-100 dark:bg-blue-900 text-xs">
            ТЕСТ: Кастомный view загружен! Время: {{ now()->format('H:i:s') }}
        </div>

        {{-- Сводка по выбранным заявкам --}}
        @php
            $selectedIds = $this->selectedTableRecords ?? [];
            $selectedCount = is_array($selectedIds) ? count($selectedIds) : 0;
        @endphp
        
        @if ($selectedCount >= 2)
            @php
                $summary = $this->getSelectedOrdersSummary();
            @endphp
            @if ($summary)
                <div 
                    wire:key="selected-summary-{{ $selectedCount }}-{{ md5(implode(',', $selectedIds)) }}" 
                    class="mt-6"
                    wire:poll.1s="refreshSummary"
                >
                    @include('filament.tables.selected-summary', ['summary' => $summary])
                </div>
            @endif
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

