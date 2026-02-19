<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.list-orders';

    protected $listeners = [
        'inlineEditCell' => 'handleInlineEditCell',
    ];

    public function getSelectedOrdersSummaryForIds(array $ids): ?array
    {
        Log::info('getSelectedOrdersSummaryForIds called', ['ids' => $ids, 'count' => count($ids)]);
        
        if (count($ids) < 2) {
            Log::debug('getSelectedOrdersSummaryForIds: less than 2 ids', ['count' => count($ids)]);
            return null;
        }

        $records = Order::query()->whereIn('id', $ids)->get();
        
        Log::info('getSelectedOrdersSummaryForIds: records found', ['count' => $records->count()]);
        
        if ($records->isEmpty()) {
            Log::debug('getSelectedOrdersSummaryForIds: no records found');
            return null;
        }

        // Вспомогательная функция для безопасного преобразования в число
        $toFloat = function ($value): float {
            if ($value === null || $value === '') {
                return 0.0;
            }
            if (is_numeric($value)) {
                return (float) $value;
            }
            // Пытаемся извлечь число из строки (например, "10000 ₽" -> 10000)
            if (is_string($value)) {
                preg_match('/[\d.,]+/', str_replace(',', '.', $value), $matches);
                return $matches ? (float) str_replace(',', '.', $matches[0]) : 0.0;
            }
            return 0.0;
        };

        return [
            'count' => $records->count(),
            'pallets_count' => $records->sum(fn (Order $order) => $toFloat($order->pallets_count)),
            'boxes_count' => $records->sum(fn (Order $order) => $toFloat(OrderResource::getSummaryDisplayValue($order, 'boxes_count'))),
            'boxes_volume' => $records->sum(fn (Order $order) => $toFloat(OrderResource::getSummaryDisplayValue($order, 'boxes_volume'))),
            'boxes_weight' => $records->sum(fn (Order $order) => $toFloat(OrderResource::getSummaryDisplayValue($order, 'boxes_weight'))),
            'palletizing_count' => $records->sum(fn (Order $order) => $toFloat($order->palletizing_count)),
            'pick' => $records->sum(fn (Order $order) => $toFloat($order->pick)),
            'delivery' => $records->sum(fn (Order $order) => $toFloat($order->delivery)),
            'additional' => $records->sum(fn (Order $order) => $toFloat($order->additional)),
            'total' => $records->sum(fn (Order $order) => $toFloat($order->total)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getExportColumns(): array
    {
        return [
            'id' => '№ заявки',
            'send_date' => 'Дата отправки',
            'created_at' => 'Дата и время',
            'agent.title' => 'Отправитель (ФИО/ИП/ООО)',
            'agent.name' => 'Контактное лицо',
            'agent.phone' => 'Номер телефона',
            'delivery_date' => 'Дата поставки на РЦ',
            'distribution' => 'РЦ и адрес',
            'payment_method' => 'Способ оплаты',
            'individual' => 'Индивидуальный расчет',
            'cargo' => 'Груз',
            'pallets_count' => 'Кол-во палет',
            'pallets_boxcount' => 'Коробов в палете',
            'pallets_weight' => 'Вес палет, кг',
            'pallets_volume' => 'Объем палет, м³',
            'boxes_count' => 'Кол-во коробов',
            'boxes_volume' => 'Объем коробов, м³',
            'boxes_weight' => 'Вес коробов, кг',
            'has_palletizing' => 'Палетирование',
            'palletizing_count' => 'Палетирование кол-во',
            'has_pickup' => 'Забор груза',
            'transfer_method_receive_date' => 'Дата привоза клиентом',
            'pick' => 'Оплата за забор, ₽',
            'transfer_method_pick_date' => 'Дата забора груза',
            'transfer_method_pick_address' => 'Адрес забора',
            'delivery' => 'Доставка, ₽',
            'additional' => 'Палетирование, ₽',
            'total' => 'Предварительная сумма, ₽',
            'cargo_comment' => 'Комментарий',
            'agent.email' => 'Email',
            'agent.inn' => 'ИНН',
            'agent.ogrn' => 'ОГРН',
        ];
    }

    /**
     * @param  array<string, string>  $columns
     */
    protected function outputExcelTable(array $columns, Collection $records): void
    {
        echo "\xEF\xBB\xBF";
        echo $this->implodeExportRow(array_values($columns)) . "\r\n";

        /** @var Order $record */
        foreach ($records as $record) {
            $row = [];

            foreach (array_keys($columns) as $column) {
                $row[] = $this->formatExportValue($record, $column);
            }

            echo $this->implodeExportRow($row) . "\r\n";
        }
    }

    /**
     * @param  array<int, string>  $values
     */
    protected function implodeExportRow(array $values): string
    {
        return implode("\t", array_map(fn ($value): string => $this->sanitizeExportValue((string) $value), $values));
    }

    protected function sanitizeExportValue(string $value): string
    {
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
        $value = str_replace("\t", ' ', $value);

        return trim($value);
    }

    protected function formatExportValue(Order $record, string $column): string
    {
        return match ($column) {
            'created_at' => $this->formatDateTimeValue($record->created_at),
            'send_date' => $this->formatDateValue($record->send_date),
            'delivery_date' => $this->formatDateValue($record->delivery_date),
            'distribution' => $record->distribution_label,
            'payment_method' => $this->getPaymentMethodLabel($record->payment_method),
            'individual' => $this->booleanToLabel((bool) $record->individual),
            'cargo' => $this->getCargoLabel($record->cargo),
            'pallets_weight', 'pallets_volume', 'boxes_volume', 'boxes_weight', 'pick', 'delivery', 'additional', 'total' => $this->numericToString($record->{$column}),
            'has_palletizing' => $this->booleanToLabel(($record->palletizing_count ?? 0) > 0),
            'palletizing_count' => $this->numericToString($record->palletizing_count),
            'has_pickup' => $this->booleanToLabel($record->transfer_method === 'pick'),
            'transfer_method_receive_date' => $this->formatDateTimeValue($record->transfer_method_receive_date),
            'transfer_method_pick_date' => $this->formatDateTimeValue($record->transfer_method_pick_date),
            default => $this->stringify(data_get($record, $column)),
        };
    }

    protected function formatDateTimeValue(mixed $value): string
    {
        return $this->formatDateGeneric($value, 'd.m.Y H:i');
    }

    protected function formatDateValue(mixed $value): string
    {
        return $this->formatDateGeneric($value, 'd.m.Y');
    }

    protected function booleanToLabel(bool $value): string
    {
        return $value ? 'Да' : 'Нет';
    }

    protected function numericToString(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return is_numeric($value) ? (string) $value : $this->stringify($value);
    }

    protected function getPaymentMethodLabel(?string $value): string
    {
        return match ($value) {
            'cash' => 'Наличные',
            'bill' => 'Безналичный',
            null => '',
            default => $value,
        };
    }

    protected function getCargoLabel(?string $value): string
    {
        return match ($value) {
            'boxes' => 'Коробки',
            'pallets' => 'Палеты',
            null => '',
            default => $value,
        };
    }

    protected function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d.m.Y H:i');
        }

        return (string) $value;
    }

    protected function formatDateGeneric(mixed $value, string $format): string
    {
        $date = $this->asCarbon($value);

        return $date?->format($format) ?? '';
    }

    /**
     * @return Collection<int, Order>
     */
    protected function getRecordsForExport(): Collection
    {
        $selectedRecords = $this->getSelectedTableRecords();

        if ($selectedRecords->isNotEmpty()) {
            return $selectedRecords->load('agent');
        }

        $query = clone $this->getTableQueryForExport();

        return $query
            ->with(['agent'])
            ->get();
    }

    public function exportSelectedRecords(Collection $records): StreamedResponse
    {
        if ($records->isEmpty()) {
            $records = $this->getSelectedTableRecords();
        }

        $records->loadMissing(['agent']);

        $columns = $this->getExportColumns();

        $fileName = 'orders-export-' . now()->format('Y-m-d-H-i-s') . '.xls';

        return response()->streamDownload(function () use ($columns, $records): void {
            $this->outputExcelTable($columns, $records);
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    protected function asCarbon(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    public function handleInlineEditCell($recordId = null, $field = null, $value = null): void
    {
        if (! $recordId || ! $field) {
            return;
        }

        // Специальная обработка для редактирования distribution
        if ($field === 'distribution_edit') {
            $this->handleDistributionEdit($recordId, $value);
            return;
        }

        if (! in_array($field, OrderResource::getInlineEditableFields(), true)) {
            return;
        }

        /** @var Order|null $record */
        $record = Order::query()->find($recordId);

        if (! $record) {
            $this->dispatch('inline-edit-cell-error', message: 'Запись не найдена');
            return;
        }

        try {
            $record->fillFields([
                $field => $value,
            ]);
            $record->save();

            $this->flushCachedTableRecords();
            $this->dispatch('inline-edit-cell-saved', field: $field, recordId: $recordId);
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('inline-edit-cell-error', message: 'Не удалось сохранить значение');
        }
    }

    protected function handleDistributionEdit($recordId, $value): void
    {
        /** @var Order|null $record */
        $record = Order::query()->find($recordId);

        if (! $record) {
            $this->dispatch('inline-edit-cell-error', message: 'Запись не найдена');
            return;
        }

        try {
            // Разбиваем значение по разделителю "|"
            // Формат: "РЦ|Адрес"
            $parts = explode('|', $value, 2);
            $distributorId = trim($parts[0] ?? '');
            $distributorCenterId = trim($parts[1] ?? '');

            $record->fillFields([
                'distributor_id' => $distributorId ?: null,
                'distributor_center_id' => $distributorCenterId ?: null,
            ]);
            $record->save();

            $this->flushCachedTableRecords();
            $this->dispatch('inline-edit-cell-saved', field: 'distribution', recordId: $recordId);
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('inline-edit-cell-error', message: 'Не удалось сохранить значение');
        }
    }

    protected function getTableContentFooter(): ?View
    {
        $summary = $this->getSelectedOrdersSummary();

        Log::debug('ListOrders::getTableContentFooter', [
            'summary_exists' => $summary !== null,
            'summary_count' => $summary['count'] ?? 0,
        ]);

        if ($summary === null) {
            return null;
        }

        return view('filament.tables.selected-summary', [
            'summary' => $summary,
        ]);
    }

    public function getSelectedOrdersSummary(): ?array
    {
        // Пробуем получить выбранные записи разными способами
        $records = null;
        
        // Способ 1: через свойство selectedTableRecords напрямую (самый надежный)
        if (property_exists($this, 'selectedTableRecords') && !empty($this->selectedTableRecords)) {
            $selectedIds = $this->selectedTableRecords;
            if (!empty($selectedIds) && is_array($selectedIds)) {
                $records = Order::query()->whereIn('id', $selectedIds)->get();
            }
        }
        
        // Способ 2: через метод getSelectedTableRecords (если способ 1 не сработал)
        if (($records === null || $records->isEmpty())) {
            try {
                $records = $this->getSelectedTableRecords(true); // true = загрузить из БД
            } catch (\Throwable $e) {
                Log::debug('ListOrders::getSelectedOrdersSummary - getSelectedTableRecords failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info('ListOrders::getSelectedOrdersSummary', [
            'selected_count_method' => $records ? $records->count() : 0,
            'selected_ids_property' => property_exists($this, 'selectedTableRecords') 
                ? (is_array($this->selectedTableRecords) ? count($this->selectedTableRecords) : 'not_array')
                : 'no_property',
            'selected_ids' => property_exists($this, 'selectedTableRecords') && is_array($this->selectedTableRecords)
                ? array_slice($this->selectedTableRecords, 0, 10) // Логируем только первые 10
                : [],
            'will_return_summary' => ($records !== null && $records->isNotEmpty() && $records->count() >= 2),
        ]);

        // Показываем сводку только при выборе 2 и более заявок
        if ($records === null || $records->isEmpty() || $records->count() < 2) {
            return null;
        }

        // Вспомогательная функция для безопасного преобразования в число
        $toFloat = function ($value): float {
            if ($value === null || $value === '') {
                return 0.0;
            }
            if (is_numeric($value)) {
                return (float) $value;
            }
            // Пытаемся извлечь число из строки (например, "10000 ₽" -> 10000)
            if (is_string($value)) {
                preg_match('/[\d.,]+/', str_replace(',', '.', $value), $matches);
                return $matches ? (float) str_replace(',', '.', $matches[0]) : 0.0;
            }
            return 0.0;
        };

        return [
            'count' => $records->count(),
            'pallets_count' => $records->sum(fn (Order $order) => $toFloat($order->pallets_count)),
            'boxes_count' => $records->sum(fn (Order $order) => $toFloat(OrderResource::getSummaryDisplayValue($order, 'boxes_count'))),
            'boxes_volume' => $records->sum(fn (Order $order) => $toFloat(OrderResource::getSummaryDisplayValue($order, 'boxes_volume'))),
            'boxes_weight' => $records->sum(fn (Order $order) => $toFloat(OrderResource::getSummaryDisplayValue($order, 'boxes_weight'))),
            'palletizing_count' => $records->sum(fn (Order $order) => $toFloat($order->palletizing_count)),
            'pick' => $records->sum(fn (Order $order) => $toFloat($order->pick)),
            'delivery' => $records->sum(fn (Order $order) => $toFloat($order->delivery)),
            'additional' => $records->sum(fn (Order $order) => $toFloat($order->additional)),
            'total' => $records->sum(fn (Order $order) => $toFloat($order->total)),
        ];
    }
}
