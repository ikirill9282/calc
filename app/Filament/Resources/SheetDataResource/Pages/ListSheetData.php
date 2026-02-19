<?php

namespace App\Filament\Resources\SheetDataResource\Pages;

use App\Filament\Resources\SheetDataResource;
use App\Models\SheetData;
use App\Support\SheetDataSchedule;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSheetData extends ListRecords
{
    protected static string $resource = SheetDataResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('changeSchedule')
                ->label('Изменить график')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->modalWidth('2xl')
                ->form([
                    Forms\Components\Section::make('Выберите направление')
                        ->schema([
                            Forms\Components\Select::make('route')
                                ->label('Направление')
                                ->options(fn () => SheetDataSchedule::routeOptions())
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $parts = SheetDataSchedule::parseRoute($state);
                                    if ($parts === null) return;

                                    $records = SheetData::where('wh', $parts[0])
                                        ->where('distributor', $parts[1])
                                        ->where('distributor_center', $parts[2])
                                        ->get();

                                    if ($records->isEmpty()) return;

                                    $set('delivery_weekdays', SheetDataSchedule::deliveryWeekdays($records));
                                    $set('shipment_weekdays', SheetDataSchedule::shipmentWeekdays($records));
                                }),
                        ]),

                    Forms\Components\Section::make('Расписание')
                        ->schema([
                            Forms\Components\CheckboxList::make('shipment_weekdays')
                                ->label('Дни забора и отгрузки')
                                ->options(SheetDataSchedule::WEEKDAY_OPTIONS)
                                ->required()
                                ->columns(4),
                            Forms\Components\CheckboxList::make('delivery_weekdays')
                                ->label('Дни доставки в РЦ')
                                ->options(SheetDataSchedule::WEEKDAY_OPTIONS)
                                ->required()
                                ->columns(4),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    $parts = SheetDataSchedule::parseRoute($data['route'] ?? null);
                    if ($parts === null) {
                        Notification::make()->title('Неверный формат направления')->danger()->send();
                        return;
                    }

                    $template = SheetData::where('wh', $parts[0])
                        ->where('distributor', $parts[1])
                        ->where('distributor_center', $parts[2])
                        ->first();

                    if (! $template) {
                        Notification::make()->title('Направление не найдено')->danger()->send();
                        return;
                    }

                    $deliveryWeekdays = SheetDataSchedule::normalizeWeekdays($data['delivery_weekdays'] ?? []);
                    $shipmentWeekdays = SheetDataSchedule::normalizeWeekdays($data['shipment_weekdays'] ?? []);

                    if (empty($deliveryWeekdays) || empty($shipmentWeekdays)) {
                        Notification::make()->title('Выберите хотя бы один день доставки и один день отгрузки')->warning()->send();
                        return;
                    }

                    $routeQuery = SheetData::where('wh', $parts[0])
                        ->where('distributor', $parts[1])
                        ->where('distributor_center', $parts[2]);

                    $dateFromRaw = (clone $routeQuery)->min('distributor_center_delivery_date');
                    $dateToRaw = (clone $routeQuery)->max('distributor_center_delivery_date');

                    if (! $dateFromRaw || ! $dateToRaw) {
                        Notification::make()->title('Для направления не найден диапазон дат')->warning()->send();
                        return;
                    }

                    $dateFrom = Carbon::parse($dateFromRaw)->startOfDay();
                    $dateTo = Carbon::parse($dateToRaw)->startOfDay();

                    SheetData::where('wh', $parts[0])
                        ->where('distributor', $parts[1])
                        ->where('distributor_center', $parts[2])
                        ->whereBetween('distributor_center_delivery_date', [$dateFrom, $dateTo])
                        ->delete();

                    $rows = SheetDataSchedule::buildRows(
                        $template,
                        $dateFrom,
                        $dateTo,
                        $deliveryWeekdays,
                        $shipmentWeekdays
                    );

                    if (empty($rows)) {
                        Notification::make()->title('Нет дат для выбранных дней')->warning()->send();
                        return;
                    }

                    foreach (array_chunk($rows, 100) as $chunk) {
                        SheetData::insert($chunk);
                    }

                    Notification::make()
                        ->title('График изменён: создано ' . count($rows) . ' записей')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
                ->label('Создать направление')
                ->icon('heroicon-o-plus')
                ->color('primary'),

        ];
    }
}
