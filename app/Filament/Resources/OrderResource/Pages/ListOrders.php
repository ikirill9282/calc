<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected $listeners = [
        'inlineEditCell' => 'handleInlineEditCell',
    ];

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportExcel')
                ->label('Экспорт в Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportExcel()),
        ];
    }

    public function exportExcel(): StreamedResponse
    {
        $columns = $this->getExportColumns();
        $records = $this->getRecordsForExport();

        $fileName = 'orders-' . now()->format('Y-m-d_H-i-s') . '.xls';

        return response()->streamDownload(function () use ($columns, $records): void {
            $this->outputExcelTable($columns, $records);
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
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
        echo '<table border="1"><thead><tr>';

        foreach ($columns as $label) {
            echo '<th>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>';
        }

        echo '</tr></thead><tbody>';

        /** @var Order $record */
        foreach ($records as $record) {
            echo '<tr>';

            foreach (array_keys($columns) as $column) {
                $value = $this->formatExportValue($record, $column);

                echo '<td>' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
            }

            echo '</tr>';
        }

        echo '</tbody></table>';
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
        $selectedKeys = collect($this->selectedTableRecords ?? [])
            ->filter(fn ($key) => filled($key))
            ->all();

        $query = clone $this->getTableQueryForExport();

        if (! empty($selectedKeys)) {
            $query->whereKey($selectedKeys);
        }

        return $query
            ->with(['agent'])
            ->get();
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

            $this->resetTable();
            $this->dispatch('inline-edit-cell-saved', field: $field, recordId: $recordId);
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('inline-edit-cell-error', message: 'Не удалось сохранить значение');
        }
    }
}
