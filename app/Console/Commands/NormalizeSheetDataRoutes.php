<?php

namespace App\Console\Commands;

use App\Models\SheetData;
use App\Support\SheetDataSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeSheetDataRoutes extends Command
{
    protected $signature = 'app:sheet-data-normalize {--max-delivery-days=7 : Max delivery weekdays (cars) per route}';

    protected $description = 'Normalize sheet_data routes and limit delivery weekdays per route';

    public function handle(): int
    {
        $maxDeliveryDays = min(7, max(1, (int) $this->option('max-delivery-days')));

        $routes = SheetData::query()
            ->select('wh', 'distributor', 'distributor_center')
            ->distinct()
            ->orderBy('wh')
            ->orderBy('distributor')
            ->orderBy('distributor_center')
            ->get();

        if ($routes->isEmpty()) {
            $this->warn('No routes found in sheet_data.');
            return self::SUCCESS;
        }

        $this->info('Routes found: ' . $routes->count());
        $this->info('Max delivery weekdays per route: ' . $maxDeliveryDays);

        $processedRoutes = 0;
        $createdRows = 0;

        foreach ($routes as $route) {
            $routeQuery = SheetData::query()
                ->where('wh', $route->wh)
                ->where('distributor', $route->distributor)
                ->where('distributor_center', $route->distributor_center);

            $records = (clone $routeQuery)->get();
            if ($records->isEmpty()) {
                continue;
            }

            $template = (clone $routeQuery)->orderBy('id')->first();
            $dateFromRaw = (clone $routeQuery)->min('distributor_center_delivery_date');
            $dateToRaw = (clone $routeQuery)->max('distributor_center_delivery_date');

            if (! $template || ! $dateFromRaw || ! $dateToRaw) {
                continue;
            }

            $deliveryWeekdays = array_slice(
                SheetDataSchedule::deliveryWeekdays($records),
                0,
                $maxDeliveryDays
            );
            $shipmentWeekdays = SheetDataSchedule::shipmentWeekdays($records);

            if (empty($deliveryWeekdays) || empty($shipmentWeekdays)) {
                continue;
            }

            $dateFrom = Carbon::parse($dateFromRaw)->startOfDay();
            $dateTo = Carbon::parse($dateToRaw)->startOfDay();

            $rows = SheetDataSchedule::buildRows(
                $template,
                $dateFrom,
                $dateTo,
                $deliveryWeekdays,
                $shipmentWeekdays
            );

            if (empty($rows)) {
                continue;
            }

            DB::transaction(function () use ($routeQuery, $dateFrom, $dateTo, $rows) {
                (clone $routeQuery)
                    ->whereBetween('distributor_center_delivery_date', [$dateFrom, $dateTo])
                    ->delete();

                foreach (array_chunk($rows, 100) as $chunk) {
                    SheetData::insert($chunk);
                }
            });

            $processedRoutes++;
            $createdRows += count($rows);
        }

        $this->info('Processed routes: ' . $processedRoutes);
        $this->info('Inserted rows: ' . $createdRows);

        return self::SUCCESS;
    }
}
