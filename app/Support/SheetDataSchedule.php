<?php

namespace App\Support;

use App\Models\SheetData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SheetDataSchedule
{
    public const WEEKDAY_OPTIONS = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        0 => 'Воскресенье',
    ];

    public const WEEKDAY_SHORT_OPTIONS = [
        1 => 'Пн',
        2 => 'Вт',
        3 => 'Ср',
        4 => 'Чт',
        5 => 'Пт',
        6 => 'Сб',
        0 => 'Вс',
    ];

    public static function routeOptions(): array
    {
        return SheetData::query()
            ->selectRaw('CONCAT(wh, " → ", distributor, " → ", distributor_center) as route')
            ->groupBy('wh', 'distributor', 'distributor_center')
            ->orderBy('wh')
            ->orderBy('distributor')
            ->orderBy('distributor_center')
            ->pluck('route', 'route')
            ->toArray();
    }

    public static function parseRoute(?string $route): ?array
    {
        if (! $route) {
            return null;
        }

        $parts = explode(' → ', $route);
        if (count($parts) !== 3) {
            return null;
        }

        return $parts;
    }

    public static function deliveryWeekdays(Collection $records): array
    {
        $configured = self::extractConfiguredWeekdays($records, 'delivery_weekdays_config');
        if (! empty($configured)) {
            return $configured;
        }

        return $records
            ->map(fn (SheetData $record) => Carbon::parse($record->distributor_center_delivery_date)->dayOfWeek)
            ->unique()
            ->values()
            ->map(fn ($day) => (int) $day)
            ->pipe(fn (Collection $days) => self::normalizeWeekdays($days->toArray()));
    }

    public static function shipmentWeekdays(Collection $records): array
    {
        $configured = self::extractConfiguredWeekdays($records, 'shipment_weekdays_config');
        if (! empty($configured)) {
            return $configured;
        }

        $days = $records
            ->map(function (SheetData $record) {
                if (! empty($record->pick_diff)) {
                    return Carbon::parse($record->pick_diff)->dayOfWeek;
                }

                if (! empty($record->delivery_diff)) {
                    return Carbon::parse($record->delivery_diff)->dayOfWeek;
                }

                return null;
            })
            ->filter(fn ($day) => $day !== null)
            ->unique()
            ->values()
            ->map(fn ($day) => (int) $day)
            ->toArray();

        $normalized = self::normalizeWeekdays($days);

        return empty($normalized) ? [0] : $normalized;
    }

    public static function resolveShipmentDate(Carbon $deliveryDate, array $shipmentWeekdays): ?Carbon
    {
        if (empty($shipmentWeekdays)) {
            return null;
        }

        $shipmentDate = $deliveryDate->copy();
        for ($attempt = 0; $attempt < 7; $attempt++) {
            if (in_array($shipmentDate->dayOfWeek, $shipmentWeekdays, true)) {
                return $shipmentDate;
            }

            $shipmentDate->subDay();
        }

        return null;
    }

    public static function buildRows(
        SheetData $template,
        Carbon $dateFrom,
        Carbon $dateTo,
        array $deliveryWeekdays,
        array $shipmentWeekdays
    ): array {
        $deliveryWeekdays = self::normalizeWeekdays($deliveryWeekdays);
        $shipmentWeekdays = self::normalizeWeekdays($shipmentWeekdays);

        $rows = [];
        $current = $dateFrom->copy()->startOfDay();

        while ($current->lte($dateTo)) {
            if (in_array($current->dayOfWeek, $deliveryWeekdays, true)) {
                $shipmentDate = self::resolveShipmentDate($current, $shipmentWeekdays);

                if ($shipmentDate !== null) {
                    $rows[] = [
                        'wh' => $template->wh,
                        'wh_address' => $template->wh_address,
                        'map' => $template->map,
                        'distributor' => $template->distributor,
                        'distributor_center' => $template->distributor_center,
                        'distributor_address' => $template->distributor_address,
                        'distributor_center_delivery_date' => $current->format('Y-m-d'),
                        'delivery_weekdays_config' => json_encode($deliveryWeekdays, JSON_UNESCAPED_UNICODE),
                        'shipment_weekdays_config' => json_encode($shipmentWeekdays, JSON_UNESCAPED_UNICODE),
                        'delivery_diff' => $shipmentDate->format('Y-m-d H:i:s'),
                        'delivery_weekend' => $template->delivery_weekend,
                        'pick_diff' => $shipmentDate->format('Y-m-d H:i:s'),
                        'pick_weekend' => $template->pick_weekend,
                        'pick_tariff_min' => $template->pick_tariff_min,
                        'pick_tariff_vol' => $template->pick_tariff_vol,
                        'pick_tariff_pallete' => $template->pick_tariff_pallete,
                        'pick_additional' => $template->pick_additional,
                        'delivery_tariff_min' => $template->delivery_tariff_min,
                        'delivery_tariff_vol' => $template->delivery_tariff_vol,
                        'delivery_tariff_pallete' => $template->delivery_tariff_pallete,
                    ];
                }
            }

            $current->addDay();
        }

        return $rows;
    }

    public static function normalizeWeekdays(array $weekdays): array
    {
        $normalized = array_values(array_unique(array_map('intval', $weekdays)));
        $normalized = array_values(array_filter(
            $normalized,
            fn (int $day): bool => array_key_exists($day, self::WEEKDAY_OPTIONS)
        ));

        usort(
            $normalized,
            fn (int $left, int $right): int => self::weekdaySortWeight($left) <=> self::weekdaySortWeight($right)
        );

        return $normalized;
    }

    protected static function extractConfiguredWeekdays(Collection $records, string $column): array
    {
        foreach ($records as $record) {
            $value = $record->{$column} ?? null;

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            if (! is_array($value)) {
                continue;
            }

            $normalized = self::normalizeWeekdays($value);
            if (! empty($normalized)) {
                return $normalized;
            }
        }

        return [];
    }

    protected static function weekdaySortWeight(int $weekday): int
    {
        return $weekday === 0 ? 7 : $weekday;
    }
}
