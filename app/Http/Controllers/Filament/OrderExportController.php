<?php

namespace App\Http\Controllers\Filament;

use App\Models\Order;
use Illuminate\Http\Response;

class OrderExportController
{
    public function show(Order $order): Response
    {
        $table = $this->buildTable($order);
        $styles = $this->tableStyles();

        $filename = 'order-' . $order->id . '.xls';
        $content = '<html><head><meta charset="utf-8"><style>' . $styles . '</style></head><body>' . $table . '</body></html>';

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function tableStyles(): string
    {
        return <<<'CSS'
            body { font-family: "Calibri", sans-serif; font-size: 12pt; }
            table { border-collapse: collapse; width: 100%; }
            td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
            td.heading { font-weight: 700; text-align: center; font-size: 16pt; }
            td.section { background: #f8f8f8; font-weight: 600; }
            td.noborder { border: none; }
            td.right { text-align: right; }
            td.bold { font-weight: 700; }
        CSS;
    }

    protected function buildTable(Order $order): string
    {
        $agent = $order->agent;
        $rows = [];

        $rows[] = '<tr><td rowspan="2">№ заявки</td><td rowspan="2">' . e($order->id) . '</td><td colspan="2" class="heading">Заявка на отгрузку</td></tr>';
        $rows[] = '<tr><td></td><td></td></tr>';

        $rows[] = $this->row('Наименование юридического лица', optional($agent)->title);
        $rows[] = $this->row('Контактное лицо', optional($agent)->name);
        $rows[] = $this->row('Телефон', optional($agent)->phone);
        $rows[] = $this->row('Время, дата составления заявки', optional($order->created_at)->format('d.m.Y H:i:s'));
        $rows[] = $this->row('Дата доставки груза', optional($order->delivery_date)->format('d.m.Y'));
        $rows[] = $this->row('Адрес забора груза', $order->transfer_method_pick_address);
        $rows[] = $this->row('Количество коробов в поставке, шт', $order->boxes_count);
        $rows[] = $this->row('Количество палет в поставке, шт.', $order->pallets_count);
        $rows[] = $this->row('Кг\м3', $order->boxes_weight);
        $rows[] = $this->row('Форма оплаты', $this->paymentMethod($order));
        $rows[] = $this->row('Склад МП', $order->warehouse_id);
        $rows[] = $this->row('Дата поставки на склад МП', optional($order->delivery_date)->format('d.m.Y'));

        $rows[] = '<tr><td colspan="4" class="noborder">&nbsp;</td></tr>';

        $rows[] = $this->row('Стоимость забора груза', $this->formatMoney($order->pick));
        $rows[] = $this->row('Стоимость перевозки', $this->formatMoney($order->delivery));
        $rows[] = $this->row('Палетирование', $this->formatMoney($order->additional));
        $rows[] = $this->row('Сумма к оплате', $this->formatMoney($order->total), ['bold']);

        $rows[] = '<tr><td colspan="4" class="noborder">&nbsp;</td></tr>';

        $rows[] = '<tr><td>Груз сдал</td><td colspan="3">(дата,подпись представителя поставщика)</td></tr>';
        $rows[] = '<tr><td colspan="4" class="noborder">&nbsp;</td></tr>';
        $rows[] = '<tr><td colspan="4" class="noborder">&nbsp;</td></tr>';
        $rows[] = '<tr><td>Груз принял</td><td colspan="3">(дата, подпись представителя ТК)</td></tr>';

        return '<table>' . implode('', $rows) . '</table>';
    }

    protected function row(string $label, $value, array $classes = []): string
    {
        $class = empty($classes) ? '' : ' class="' . implode(' ', $classes) . '"';
        $value = $value ?? '';

        return '<tr><td>' . e($label) . '</td><td colspan="3"' . $class . '>' . e($value) . '</td></tr>';
    }

    protected function paymentMethod(Order $order): string
    {
        return match ($order->payment_method) {
            'cash' => 'Наличными при отправке',
            'bill' => 'По счету',
            default => (string) $order->payment_method,
        };
    }

    protected function formatMoney($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2, ',', ' ') . ' руб.';
    }
}
