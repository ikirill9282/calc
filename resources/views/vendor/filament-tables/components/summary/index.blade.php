@props([
    'actions' => false,
    'actionsPosition' => null,
    'columns',
    'extraHeadingColumn' => false,
    'groupColumn' => null,
    'groupsOnly' => false,
    'placeholderColumns' => true,
    'pluralModelLabel',
    'recordCheckboxPosition' => null,
    'records',
    'selectionEnabled' => false,
])

@php
    use Filament\Support\Enums\Alignment;
    use Filament\Tables\Columns\Column;
    use Filament\Tables\Enums\ActionsPosition;
    use Filament\Tables\Enums\RecordCheckboxPosition;

    if ($groupsOnly && $groupColumn) {
        $columns = collect($columns)
            ->reject(fn (Column $column): bool => $column->getName() === $groupColumn)
            ->all();
    }

    $livewire = $this->getLivewire();

    $selectedRecordKeys = [];

    if ($livewire) {
        if (method_exists($livewire, 'getSelectedTableRecords')) {
            $selectedRecordKeys = $livewire->getSelectedTableRecords(false)->all();
        } elseif (property_exists($livewire, 'selectedTableRecords') && is_array($livewire->selectedTableRecords)) {
            $selectedRecordKeys = $livewire->selectedTableRecords;
        }
    }

    $selectedRecordKeys = array_filter($selectedRecordKeys, fn ($key) => $key !== null && $key !== '');
    $selectedCount = count($selectedRecordKeys);

    $summaryQuery = $this->getAllTableSummaryQuery();
@endphp

@if ($selectedCount > 0)
    <x-filament-tables::row
        class="fi-ta-summary-header-row bg-gray-50 dark:bg-white/5"
    >
        @if ($placeholderColumns && $actions && in_array($actionsPosition, [ActionsPosition::BeforeCells, ActionsPosition::BeforeColumns]))
            <td></td>
        @endif

        @if ($placeholderColumns && $selectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
            <td></td>
        @endif

        @if ($extraHeadingColumn)
            <x-filament-tables::summary.header-cell>
                {{ __('filament-tables::table.summary.heading', ['label' => $pluralModelLabel]) }}
            </x-filament-tables::summary.header-cell>
        @endif

        @foreach ($columns as $column)
            @php
                $columnHasSummary = $column->hasSummary($summaryQuery);
            @endphp

            @if ($placeholderColumns || $columnHasSummary)
                @php
                    $alignment = $column->getAlignment() ?? Alignment::Start;

                    if (! $alignment instanceof Alignment) {
                        $alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
                    }

                    $hasColumnHeaderLabel = (! $placeholderColumns) || $columnHasSummary;
                @endphp

                <x-filament-tables::summary.header-cell
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes($column->getExtraHeaderAttributeBag())
                            ->class([
                                'whitespace-nowrap' => ! $column->isHeaderWrapped(),
                                'whitespace-normal' => $column->isHeaderWrapped(),
                                match ($alignment) {
                                    Alignment::Start => 'text-start',
                                    Alignment::Center => 'text-center',
                                    Alignment::End => 'text-end',
                                    Alignment::Left => 'text-left',
                                    Alignment::Right => 'text-right',
                                    Alignment::Justify, Alignment::Between => 'text-justify',
                                    default => $alignment,
                                } => (! ($loop->first && (! $extraHeadingColumn))) && $hasColumnHeaderLabel,
                            ])
                    "
                >
                    @if ($loop->first && (! $extraHeadingColumn))
                        {{ __('Выбранные заявки') }}
                    @elseif ($hasColumnHeaderLabel)
                        {{ $column->getLabel() }}
                    @endif
                </x-filament-tables::summary.header-cell>
            @endif
        @endforeach

        @if ($placeholderColumns && $actions && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
            <td></td>
        @endif

        @if ($placeholderColumns && $selectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
            <td></td>
        @endif
    </x-filament-tables::row>

    <x-filament-tables::summary.row
        :actions="$actions"
        :actions-position="$actionsPosition"
        :columns="$columns"
        :extra-heading-column="$extraHeadingColumn"
        :heading="__('Выбрано: :count', ['count' => $selectedCount])"
        :placeholder-columns="$placeholderColumns"
        :query="$summaryQuery"
        :record-checkbox-position="$recordCheckboxPosition"
        :selected-state="[]"
        :selection-enabled="$selectionEnabled"
    />
@endif
