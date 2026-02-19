<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SheetData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderCostCalculator
{
    public function __construct(
        protected Order $order,
    ) {
    }

    public static function for(Order $order): self
    {
        return new self($order);
    }

    /**
     * @return array{
     *     pick: ?int,
     *     delivery: ?int|float,
     *     additional: ?int,
     *     total: ?int
     * }
     */
    public function calculate(): array
    {
        if ($this->order->individual) {
            return [
                'pick' => null,
                'delivery' => null,
                'additional' => null,
                'total' => null,
            ];
        }

        if (! $this->hasTariffContext()) {
            return [
                'pick' => $this->order->pick,
                'delivery' => $this->order->delivery,
                'additional' => $this->order->additional,
                'total' => $this->order->total,
            ];
        }

        $pick = $this->calculatePickAmount();
        $delivery = $this->calculateDeliveryAmount();
        $additional = $this->calculateAdditionalAmount();

        $pick ??= $this->order->pick;
        $delivery ??= $this->order->delivery;
        $additional ??= $this->order->additional;

        $pick = $pick !== null ? $this->normalizeMoney($pick) : null;
        $delivery = $delivery !== null ? $this->normalizeMoney($delivery) : null;
        $additional = $additional !== null ? $this->normalizeMoney($additional) : null;

        $totalBase = $this->normalizeMoney(($pick ?? 0) + ($delivery ?? 0) + ($additional ?? 0));

        return [
            'pick' => $pick,
            'delivery' => $delivery,
            'additional' => $additional,
            'total' => (int) ceil($totalBase),
        ];
    }

    protected function hasTariffContext(): bool
    {
        return filled($this->order->warehouse_id)
            && filled($this->order->distributor_id)
            && filled($this->order->distributor_center_id)
            && $this->deliveryDateString() !== null;
    }

    protected function deliveryDateString(): ?string
    {
        if (blank($this->order->delivery_date)) {
            return null;
        }

        try {
            return Carbon::parse($this->order->delivery_date)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function calculatePickAmount(): ?int
    {
        if ($this->order->transfer_method !== 'pick') {
            return 0;
        }

        $date = $this->deliveryDateString();

        if ($date === null) {
            return 0;
        }

        $min = $this->normalizeFloat($this->baseSheetQuery()
            ->where('distributor_center_delivery_date', $date)
            ->select('pick_tariff_min')
            ->first()
            ?->pick_tariff_min ?? 0);

        $tariffs = $this->resolveTariffs(
            ['pick_tariff_vol', 'pick_tariff_pallete'],
            requireDeliveryDateMatch: true,
        );

        if ($tariffs === null) {
            return $min ? (int) ceil($this->normalizeMoney($min)) : 0;
        }

        $result = 0;

        if ($this->canCalculateBoxes()) {
            $volume = $this->normalizeFloat($this->order->boxes_volume);
            $volume = $this->roundVolumeStep($volume);
            $costVolume = $this->normalizeMoney($volume * $this->normalizeFloat($tariffs['pick_tariff_vol'] ?? 0));
            $result += max($min, $costVolume);
        }

        if ($this->canCalculatePallets()) {
            $costPallet = $this->normalizeFloat($this->order->pallets_count) * $this->normalizeFloat($tariffs['pick_tariff_pallete'] ?? 0);

            return (int) ceil($this->normalizeMoney($costPallet));
        }

        return (int) ceil($this->normalizeMoney($result));
    }

    protected function calculateDeliveryAmount(): ?float
    {
        $tariffs = $this->resolveTariffs(
            ['delivery_tariff_min', 'delivery_tariff_vol', 'delivery_tariff_pallete'],
        );

        if ($tariffs === null) {
            return null;
        }

        $result = 0;

        if ($this->canCalculateBoxes()) {
            $volume = $this->normalizeFloat($this->order->boxes_volume);
            $volume = $this->roundVolumeStep($volume);
            $costVolume = $this->normalizeMoney($volume * $this->normalizeFloat($tariffs['delivery_tariff_vol'] ?? 0));
            $result += max($this->normalizeFloat($tariffs['delivery_tariff_min'] ?? 0), $costVolume);
        }

        if ($this->canCalculatePallets()) {
            $costPallet = $this->normalizeFloat($this->order->pallets_count) * $this->normalizeFloat($tariffs['delivery_tariff_pallete'] ?? 0);

            return (int) ceil($this->normalizeMoney($costPallet));
        }

        return $this->normalizeMoney($result);
    }

    protected function calculateAdditionalAmount(): int
    {
        if ($this->order->individual) {
            return 0;
        }

        if ($this->order->cargo === 'boxes') {
            return 0;
        }

        if (! $this->canCalculateBoxes() && ! $this->canCalculatePallets()) {
            return 0;
        }

        $rate = match ($this->order->palletizing_type) {
            'single', 'pallet' => 800,
            default => 0,
        };

        return $rate
            ? (int) ($this->normalizeFloat($this->order->palletizing_count) * $rate)
            : 0;
    }

    protected function canCalculateBoxes(): bool
    {
        return $this->order->cargo === 'boxes'
            && $this->normalizeFloat($this->order->boxes_count) > 0
            && $this->normalizeFloat($this->order->boxes_volume) > 0;
    }

    protected function canCalculatePallets(): bool
    {
        return $this->order->cargo === 'pallets'
            && $this->normalizeFloat($this->order->pallets_count) > 0;
    }

    protected function normalizeFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value) && $value !== '') {
            return (float) str_replace(',', '.', $value);
        }

        return 0.0;
    }

    protected function roundVolumeStep(float $volume): float
    {
        if ($volume <= 0) {
            return 0.0;
        }

        return $this->normalizeMoney(ceil($volume / 0.05) * 0.05);
    }

    protected function normalizeMoney(mixed $value): float
    {
        return round($this->normalizeFloat($value), 2);
    }

    protected function baseSheetQuery(): Builder
    {
        return SheetData::query()
            ->where(DB::raw('CONCAT(wh, " ", wh_address)'), $this->order->warehouse_id)
            ->where('distributor', $this->order->distributor_id)
            ->where(DB::raw('CONCAT(distributor_center, " ", distributor_address)'), $this->order->distributor_center_id);
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<string, float>|null
     */
    protected function resolveTariffs(array $columns, bool $requireDeliveryDateMatch = false): ?array
    {
        $query = $this->baseSheetQuery();

        if ($requireDeliveryDateMatch) {
            $date = $this->deliveryDateString();

            if ($date === null) {
                return null;
            }

            $query->where('distributor_center_delivery_date', $date);
        }

        /** @var Collection<int, \App\Models\SheetData> $records */
        $records = $query
            ->select($columns)
            ->groupBy($columns)
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        if ($records->count() > 1) {
            $values = [];

            foreach ($columns as $column) {
                $values[$column] = $records->max($column);
            }

            return $values;
        }

        $model = $records->first();

        return $model?->only($columns);
    }
}
