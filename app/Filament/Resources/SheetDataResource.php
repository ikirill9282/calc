<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SheetDataResource\Pages;
use App\Models\SheetData;
use App\Support\SheetDataSchedule;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SheetDataResource extends Resource
{
    protected static ?string $model = SheetData::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Конфигурация';

    protected static ?string $modelLabel = 'Тариф';

    protected static ?string $pluralModelLabel = 'Конфигурация';

    protected static ?string $navigationGroup = 'Система';

    protected static ?int $navigationSort = 20;

    protected static array $routeWeekdaysCache = [];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Склад и маршрут')
                    ->schema([
                        Forms\Components\TextInput::make('wh')
                            ->label('Склад отправления')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('wh_address')
                            ->label('Адрес отправления')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('map')
                            ->label('Ссылка на карту')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('distributor')
                            ->label('Маркетплейс (МП)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('distributor_center')
                            ->label('РЦ (склад доставки)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('distributor_address')
                            ->label('Адрес РЦ')
                            ->maxLength(500),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Даты и расписание')
                    ->schema([
                        Forms\Components\DatePicker::make('distributor_center_delivery_date')
                            ->label('Дата доставки в РЦ')
                            ->required()
                            ->displayFormat('d.m.Y'),
                        Forms\Components\DateTimePicker::make('delivery_diff')
                            ->label('Крайняя дата отгрузки на склад')
                            ->displayFormat('d.m.Y'),
                        Forms\Components\Select::make('delivery_weekend')
                            ->label('Скрывать выходные (доставка)')
                            ->options([
                                0 => 'Нет',
                                1 => 'Да',
                            ])
                            ->default(0),
                        Forms\Components\DateTimePicker::make('pick_diff')
                            ->label('Крайняя дата забора груза')
                            ->displayFormat('d.m.Y'),
                        Forms\Components\TextInput::make('pick_weekend')
                            ->label('Скрывать выходные (забор)')
                            ->default('0'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Тарифы на забор груза')
                    ->schema([
                        Forms\Components\TextInput::make('pick_tariff_min')
                            ->label('Тариф забор мин. стоимость')
                            ->numeric()
                            ->default(0)
                            ->suffix('₽'),
                        Forms\Components\TextInput::make('pick_tariff_vol')
                            ->label('Тариф забор "Коробки" (м³)')
                            ->numeric()
                            ->default(0)
                            ->suffix('₽'),
                        Forms\Components\TextInput::make('pick_tariff_pallete')
                            ->label('Тариф забор "Паллеты"')
                            ->numeric()
                            ->default(0)
                            ->suffix('₽'),
                        Forms\Components\TextInput::make('pick_additional')
                            ->label('Забор по области/региону')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Тарифы на доставку в РЦ')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_tariff_min')
                            ->label('Тариф доставка мин. (фикс до 0.1м³)')
                            ->numeric()
                            ->default(0)
                            ->suffix('₽'),
                        Forms\Components\TextInput::make('delivery_tariff_vol')
                            ->label('Тариф доставка "Коробки" (м³)')
                            ->numeric()
                            ->default(0)
                            ->suffix('₽'),
                        Forms\Components\TextInput::make('delivery_tariff_pallete')
                            ->label('Тариф доставка "Паллеты"')
                            ->numeric()
                            ->default(0)
                            ->suffix('₽'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextInputColumn::make('wh')
                    ->label('Склад')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('wh_address')
                    ->label('Адрес склада')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\SelectColumn::make('distributor')
                    ->label('МП')
                    ->searchable()
                    ->sortable()
                    ->options([
                        'Wildberries' => 'Wildberries',
                        'Ozon' => 'Ozon',
                        'Яндекс.Маркет' => 'Яндекс.Маркет',
                    ]),
                Tables\Columns\TextInputColumn::make('distributor_center')
                    ->label('РЦ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('distributor_address')
                    ->label('Адрес РЦ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_weekdays')
                    ->label('Доставка (дни)')
                    ->state(fn (SheetData $record): array => static::weekdayShortLabels(static::getRecordWeekdays($record)['delivery']))
                    ->badge()
                    ->action(
                        Tables\Actions\Action::make('editDeliveryWeekdays')
                            ->label('Изменить дни доставки')
                            ->icon('heroicon-o-calendar-days')
                            ->modalHeading('Дни доставки в РЦ')
                            ->modalSubmitActionLabel('Сохранить')
                            ->fillForm(fn (SheetData $record): array => [
                                'delivery_weekdays' => static::getRecordWeekdays($record)['delivery'],
                            ])
                            ->form([
                                Forms\Components\CheckboxList::make('delivery_weekdays')
                                    ->label('Дни доставки в РЦ')
                                    ->options(SheetDataSchedule::WEEKDAY_OPTIONS)
                                    ->required()
                                    ->columns(4),
                            ])
                            ->action(function (SheetData $record, array $data): void {
                                $deliveryWeekdays = static::normalizeWeekdays($data['delivery_weekdays'] ?? []);

                                if (empty($deliveryWeekdays)) {
                                    Notification::make()->title('Выберите хотя бы один день доставки')->warning()->send();
                                    return;
                                }

                                $shipmentWeekdays = static::getRecordWeekdays($record)['shipment'];
                                $shipmentDate = SheetDataSchedule::resolveShipmentDate(
                                    Carbon::parse($record->distributor_center_delivery_date),
                                    $shipmentWeekdays
                                );

                                if ($shipmentDate === null) {
                                    Notification::make()->title('Не удалось рассчитать дату отгрузки для этой строки')->warning()->send();
                                    return;
                                }

                                $record->forceFill([
                                    'delivery_weekdays_config' => $deliveryWeekdays,
                                    'shipment_weekdays_config' => $shipmentWeekdays,
                                    'delivery_diff' => $shipmentDate->format('Y-m-d H:i:s'),
                                    'pick_diff' => $shipmentDate->format('Y-m-d H:i:s'),
                                ])->save();

                                static::invalidateRouteWeekdaysCache($record);

                                Notification::make()
                                    ->title('Дни доставки обновлены для строки')
                                    ->success()
                                    ->send();
                            })
                    ),
                Tables\Columns\TextColumn::make('shipment_weekdays')
                    ->label('Отгрузка/забор (дни)')
                    ->state(fn (SheetData $record): array => static::weekdayShortLabels(static::getRecordWeekdays($record)['shipment']))
                    ->badge()
                    ->action(
                        Tables\Actions\Action::make('editShipmentWeekdays')
                            ->label('Изменить дни отгрузки/забора')
                            ->icon('heroicon-o-calendar-days')
                            ->modalHeading('Дни забора и отгрузки')
                            ->modalSubmitActionLabel('Сохранить')
                            ->fillForm(fn (SheetData $record): array => [
                                'shipment_weekdays' => static::getRecordWeekdays($record)['shipment'],
                            ])
                            ->form([
                                Forms\Components\CheckboxList::make('shipment_weekdays')
                                    ->label('Дни забора и отгрузки')
                                    ->options(SheetDataSchedule::WEEKDAY_OPTIONS)
                                    ->required()
                                    ->columns(4),
                            ])
                            ->action(function (SheetData $record, array $data): void {
                                $shipmentWeekdays = static::normalizeWeekdays($data['shipment_weekdays'] ?? []);

                                if (empty($shipmentWeekdays)) {
                                    Notification::make()->title('Выберите хотя бы один день отгрузки/забора')->warning()->send();
                                    return;
                                }

                                $deliveryWeekdays = static::getRecordWeekdays($record)['delivery'];
                                $shipmentDate = SheetDataSchedule::resolveShipmentDate(
                                    Carbon::parse($record->distributor_center_delivery_date),
                                    $shipmentWeekdays
                                );

                                if ($shipmentDate === null) {
                                    Notification::make()->title('Не удалось рассчитать дату отгрузки для этой строки')->warning()->send();
                                    return;
                                }

                                $record->forceFill([
                                    'delivery_weekdays_config' => $deliveryWeekdays,
                                    'shipment_weekdays_config' => $shipmentWeekdays,
                                    'delivery_diff' => $shipmentDate->format('Y-m-d H:i:s'),
                                    'pick_diff' => $shipmentDate->format('Y-m-d H:i:s'),
                                ])->save();

                                static::invalidateRouteWeekdaysCache($record);

                                Notification::make()
                                    ->title('Дни отгрузки/забора обновлены для строки')
                                    ->success()
                                    ->send();
                            })
                    ),
                Tables\Columns\SelectColumn::make('delivery_weekend')
                    ->label('Дост. вых.')
                    ->options([
                        0 => 'Нет',
                        1 => 'Да',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\SelectColumn::make('pick_weekend')
                    ->label('Забор вых.')
                    ->options([
                        '0' => 'Нет',
                        '1' => 'Да',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextInputColumn::make('pick_tariff_min')
                    ->label('Забор мин.')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('pick_tariff_vol')
                    ->label('Забор м³')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('pick_tariff_pallete')
                    ->label('Забор палл.')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('pick_additional')
                    ->label('Забор обл.')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextInputColumn::make('delivery_tariff_min')
                    ->label('Дост. мин.')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('delivery_tariff_vol')
                    ->label('Дост. м³')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('delivery_tariff_pallete')
                    ->label('Дост. палл.')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('wh')
                    ->label('Склад')
                    ->options(fn () => SheetData::query()->distinct()->pluck('wh', 'wh')->toArray())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('distributor')
                    ->label('Маркетплейс')
                    ->options(fn () => SheetData::query()->distinct()->pluck('distributor', 'distributor')->toArray()),
                Tables\Filters\SelectFilter::make('distributor_center')
                    ->label('РЦ')
                    ->options(fn () => SheetData::query()->distinct()->orderBy('distributor_center')->pluck('distributor_center', 'distributor_center')->toArray())
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('changeSchedule')
                    ->label('Изменить график')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->modalWidth('2xl')
                    ->fillForm(function (SheetData $record) {
                        $weekdays = static::getRouteWeekdays($record);

                        return [
                            'delivery_weekdays' => $weekdays['delivery'],
                            'shipment_weekdays' => $weekdays['shipment'],
                        ];
                    })
                    ->form([
                        Forms\Components\Placeholder::make('route_info')
                            ->label('Маршрут')
                            ->content(fn ($record) => "{$record->wh} → {$record->distributor} → {$record->distributor_center}"),
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
                    ->action(function ($record, array $data) {
                        $deliveryWeekdays = static::normalizeWeekdays($data['delivery_weekdays'] ?? []);
                        $shipmentWeekdays = static::normalizeWeekdays($data['shipment_weekdays'] ?? []);

                        if (empty($deliveryWeekdays) || empty($shipmentWeekdays)) {
                            Notification::make()->title('Выберите хотя бы один день доставки и один день отгрузки')->warning()->send();
                            return;
                        }

                        $count = static::rebuildRouteScheduleForRecord(
                            $record,
                            $deliveryWeekdays,
                            $shipmentWeekdays
                        );

                        if ($count === 0) {
                            Notification::make()->title('Нет дат для выбранных дней недели')->warning()->send();
                            return;
                        }

                        Notification::make()
                            ->title('График изменён: создано ' . $count . ' записей')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ReplicateAction::make()
                    ->label('Дублировать')
                    ->icon('heroicon-o-document-duplicate'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('distributor_center', 'asc')
            ->striped()
            ->paginated([25, 50, 100, 'all']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSheetData::route('/'),
            'create' => Pages\CreateSheetData::route('/create'),
            'edit' => Pages\EditSheetData::route('/{record}/edit'),
        ];
    }

    protected static function getRouteRecords(SheetData $record)
    {
        return static::getRouteQuery($record)->get();
    }

    protected static function rebuildRouteScheduleForRecord(
        SheetData $record,
        array $deliveryWeekdays,
        array $shipmentWeekdays
    ): int {
        $deliveryWeekdays = static::normalizeWeekdays($deliveryWeekdays);
        $shipmentWeekdays = static::normalizeWeekdays($shipmentWeekdays);

        if (empty($deliveryWeekdays) || empty($shipmentWeekdays)) {
            return 0;
        }

        $routeQuery = static::getRouteQuery($record);

        $dateFromRaw = (clone $routeQuery)->min('distributor_center_delivery_date');
        $dateToRaw = (clone $routeQuery)->max('distributor_center_delivery_date');

        if (! $dateFromRaw || ! $dateToRaw) {
            return 0;
        }

        $template = (clone $routeQuery)->orderBy('id')->first() ?? $record;
        $dateFrom = Carbon::parse($dateFromRaw)->startOfDay();
        $dateTo = Carbon::parse($dateToRaw)->startOfDay();

        (clone $routeQuery)
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
            return 0;
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            SheetData::insert($chunk);
        }

        static::cacheRouteWeekdays($record, $deliveryWeekdays, $shipmentWeekdays);

        return count($rows);
    }

    protected static function getRouteQuery(SheetData $record)
    {
        return SheetData::query()
            ->where('wh', $record->wh)
            ->where('distributor', $record->distributor)
            ->where('distributor_center', $record->distributor_center);
    }

    protected static function getRouteWeekdays(SheetData $record): array
    {
        $cacheKey = static::getRouteCacheKey($record);

        if (! isset(static::$routeWeekdaysCache[$cacheKey])) {
            $records = static::getRouteRecords($record);
            static::$routeWeekdaysCache[$cacheKey] = [
                'delivery' => static::normalizeWeekdays(SheetDataSchedule::deliveryWeekdays($records)),
                'shipment' => static::normalizeWeekdays(SheetDataSchedule::shipmentWeekdays($records)),
            ];
        }

        return static::$routeWeekdaysCache[$cacheKey];
    }

    protected static function getRecordWeekdays(SheetData $record): array
    {
        $delivery = static::normalizeWeekdays((array) ($record->delivery_weekdays_config ?? []));
        if (empty($delivery)) {
            $delivery = [Carbon::parse($record->distributor_center_delivery_date)->dayOfWeek];
        }
        $delivery = static::normalizeWeekdays($delivery);

        $shipment = static::normalizeWeekdays((array) ($record->shipment_weekdays_config ?? []));
        if (empty($shipment)) {
            $sourceDate = $record->pick_diff ?: $record->delivery_diff ?: $record->distributor_center_delivery_date;
            $shipment = [Carbon::parse($sourceDate)->dayOfWeek];
        }
        $shipment = static::normalizeWeekdays($shipment);

        return [
            'delivery' => $delivery,
            'shipment' => empty($shipment) ? [0] : $shipment,
        ];
    }

    protected static function cacheRouteWeekdays(
        SheetData $record,
        array $deliveryWeekdays,
        array $shipmentWeekdays
    ): void {
        static::$routeWeekdaysCache[static::getRouteCacheKey($record)] = [
            'delivery' => static::normalizeWeekdays($deliveryWeekdays),
            'shipment' => static::normalizeWeekdays($shipmentWeekdays),
        ];
    }

    protected static function invalidateRouteWeekdaysCache(SheetData $record): void
    {
        unset(static::$routeWeekdaysCache[static::getRouteCacheKey($record)]);
    }

    protected static function getRouteCacheKey(SheetData $record): string
    {
        return implode('|', [
            (string) $record->wh,
            (string) $record->distributor,
            (string) $record->distributor_center,
        ]);
    }

    protected static function normalizeWeekdays(array $weekdays): array
    {
        return SheetDataSchedule::normalizeWeekdays($weekdays);
    }

    protected static function weekdayShortLabels(array $weekdays): array
    {
        return array_map(
            fn (int $day): string => SheetDataSchedule::WEEKDAY_SHORT_OPTIONS[$day] ?? (string) $day,
            static::normalizeWeekdays($weekdays)
        );
    }

}
