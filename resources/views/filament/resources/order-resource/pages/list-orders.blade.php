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
            \Illuminate\Support\Facades\Log::info('View render - selected count', ['count' => $selectedCount, 'ids' => $selectedIds]);
        @endphp
        
        {{-- Отладка --}}
        <div class="mt-4 p-2 bg-green-100 dark:bg-green-900 text-xs">
            Отладка: selectedCount = {{ $selectedCount }}, IDs = {{ is_array($selectedIds) ? implode(', ', array_slice($selectedIds, 0, 10)) : 'не массив' }}
        </div>
        
        @if ($selectedCount >= 2)
            @php
                $summary = $this->getSelectedOrdersSummary();
                \Illuminate\Support\Facades\Log::info('View render - summary', ['summary_exists' => $summary !== null, 'count' => $summary['count'] ?? 0]);
            @endphp
            
            @if ($summary)
                <div 
                    wire:key="selected-summary-{{ $selectedCount }}-{{ md5(implode(',', $selectedIds)) }}" 
                    class="mt-6"
                >
                    @include('filament.tables.selected-summary', ['summary' => $summary])
                </div>
            @else
                <div class="mt-4 p-2 bg-red-100 dark:bg-red-900 text-xs">
                    Отладка: summary = null (метод вернул null)
                </div>
            @endif
        @else
            <div class="mt-4 p-2 bg-yellow-100 dark:bg-yellow-900 text-xs">
                Отладка: selectedCount ({{ $selectedCount }}) < 2, сводка не показывается
            </div>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

