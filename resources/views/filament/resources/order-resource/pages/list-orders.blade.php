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
            $summary = $selectedCount >= 2 ? $this->getSelectedOrdersSummary() : null;
        @endphp
        @if ($summary)
            <div 
                wire:key="selected-summary-{{ $selectedCount }}-{{ implode(',', array_slice($selectedIds, 0, 5)) }}" 
                class="mt-6"
            >
                @include('filament.tables.selected-summary', ['summary' => $summary])
            </div>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

