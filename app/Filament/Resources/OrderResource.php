<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Tables\Summarizers\ConditionalSum;
use Filament\Navigation\NavigationItem;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Заявки';

    protected static ?string $modelLabel = 'Заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

    protected static ?int $navigationSort = 1;

    /**
     * @var array<string>
     */
    protected static array $inlineEditableFields = [
        'send_date',
        'delivery_date',
        'payment_method',
        'cargo',
        'pallets_count',
        'boxes_count',
        'boxes_volume',
        'boxes_weight',
        'palletizing_count',
        'transfer_method',
        'transfer_method_pick_date',
        'transfer_method_pick_address',
        'transfer_method_receive_date',
        'pick',
        'delivery',
        'additional',
        'total',
        'cargo_comment',
        'pallets_boxcount',
        'pallets_volume',
        'pallets_weight',
        'driver_name',
		'cash_accepted',
        'distribution',
    ];

	protected static array $individualBaseTariffs = [
		'Wildberries - Симферополь' => 1500.0,
		'Wildberries - Краснодар' => 5000.0,
		'Wildberries - Невинномысск' => 6000.0,
		'Wildberries - Подольск' => 6500.0,
		'Wildberries - Электросталь' => 7500.0,
		'Wildberries - Коледино' => 6500.0,
		'Wildberries - Тула' => 6500.0,
		'Wildberries - Рязань' => 7000.0,
		'Wildberries - Тамбов' => 7000.0,
		'Wildberries - Казань' => 6500.0,
		'Wildberries - Санкт-Петербург' => 9000.0,
		'Wildberries - Екатеринбург' => 15000.0,
		'Wildberries - Новосемейкино' => 7500.0,
		'Wildberries - Волгоград' => 7500.0,
		'Wildberries - Сарапул' => 9000.0,
		'Wildberries - Владимир' => 7500.0,
		'Wildberries - Воронеж' => 7000.0,
		'Wildberries - Пенза' => 7500.0,
		'Ozon - Екатеринбург' => 15000.0,
		'Ozon - Ростов-на-Дону' => 5000.0,
		'Ozon - Казань' => 6500.0,
		'Ozon - Хоругвино' => 7500.0,
		'Ozon - Санкт-Петербург' => 9000.0,
		'Ozon - Пушкино-1' => 6500.0,
		'Ozon - Пушкино-2' => 6500.0,
		'Ozon - Софьино' => 6500.0,
		'Ozon - Жуковский' => 6500.0,
		'Ozon - Гривно' => 6500.0,
		'Ozon - Адыгея' => 6000.0,
		'Ozon - Невинномысск' => 6000.0,
		'Ozon - Пушкино' => 6500.0,
		'Ozon - Петровское' => 7500.0,
		'Ozon - Ногинск' => 7500.0,
		'Ozon - Ростов-на-Дону 2' => 5000.0,
	];

	protected static array $individualWeightTariffs = [
		'Wildberries - Краснодар' => 16.67,
		'Wildberries - Невинномысск' => 20.0,
		'Wildberries - Подольск' => 21.67,
		'Wildberries - Электросталь' => 25.0,
		'Wildberries - Коледино' => 21.67,
		'Wildberries - Тула' => 21.67,
		'Wildberries - Рязань' => 23.33,
		'Wildberries - Тамбов' => 23.33,
		'Wildberries - Казань' => 21.67,
		'Wildberries - Санкт-Петербург' => 30.0,
		'Wildberries - Екатеринбург' => 50.0,
		'Wildberries - Новосемейкино' => 25.0,
		'Wildberries - Волгоград' => 25.0,
		'Wildberries - Сарапул' => 30.0,
		'Wildberries - Владимир' => 25.0,
		'Wildberries - Воронеж' => 23.33,
		'Wildberries - Пенза' => 25.0,
		'Ozon - Екатеринбург' => 50.0,
		'Ozon - Ростов-на-Дону' => 16.67,
		'Ozon - Казань' => 21.67,
        'Ozon - Хоругвино' => 25.0,
		'Ozon - Санкт-Петербург' => 30.0,
		'Ozon - Пушкино-1' => 21.67,
		'Ozon - Пушкино-2' => 21.67,
		'Ozon - Софьино' => 21.67,
		'Ozon - Жуковский' => 21.67,
		'Ozon - Гривно' => 21.67,
		'Ozon - Адыгея' => 20.0,
		'Ozon - Невинномысск' => 20.0,
		'Ozon - Петровское' => 25.0,
		'Ozon - Ногинск' => 25.0,
		'Ozon - Ростов-на-Дону 2' => 16.67,
	];

	protected static array $individualOverweightRates = [
		'Краснодар Краснодар, ул. Тихорецкая, 40с1' => 12.5,
		'Невинномысск Невинномысск, ул. Тимирязева 16' => 15.0,
		'Подольск Wildberries' => 16.25,
		'Электросталь Поселок Случайный, территория Массив 3,5' => 18.75,
		'Коледино Индустриальный парк Коледино, Троицкая улица, 20' => 16.25,
		'Тула (Алексин) Тульская обл., г.о. Алексин, территория ВБ Алексин, 1' => 16.25,
		'Рязань Индустриальный промышленный парк Рязанский, Тюшевское сельское поселение, Рязанский р-н' => 17.5,
		'Тамбов (Котовск) Тамбовская обл., муниципальное образование г. Котовск, р-н индустриальный парк Котовск, 3/8' => 17.5,
		'Казань Зеленодольск, промышленная площадка Зеленодольск, 20' => 16.25,
		'Санкт-Петербург (Уткина Заводь) Wildberries' => 22.5,
		'Екатеринбург - Перспективный 12/2 Екатеринбург, ул. Перспективная 12/2' => 37.5,
		'Екатеринбург - Испытателей 14Г Екатеринбург, ул. Испытателей, 14Г' => 37.5,
		'г. Пенза, ул. Ульяновская, 85А' => 18.75,
		'Екатеринбург Ozon' => 37.5,
		'Ростов-на-Дону г. Ростов-на-Дону, Аксайский р-н, х. Ленина, ул. Логопарк 5' => 12.5,
		'Ростов-на-Дону (Кроссдок) г. Ростов-на-Дону, Аксайский р-н, х. Ленина, ул. Логопарк 5' => 12.5,
		'Казань Ozon' => 16.25,
		'Казань (Кроссдок) Ozon' => 16.25,
		'Хоругвино Ozon' => 18.75,
		'Хоругвино (Кроссдок) Ozon' => 18.75,
		'Санкт-Петербург (Шушары) Ozon' => 22.5,
		'Санкт-Петербург (Петро-Славянка) Ozon' => 22.5,
		'Санкт-Петербург (Бугры) Ozon' => 22.5,
		'Санкт-Петербург (Колпино) Ozon' => 22.5,
		'Пушкино-1 Ozon' => 16.25,
		'Пушкино-1 (Кроссдок) Ozon' => 16.25,
		'Ozon Пушкино-2' => 16.25,
		'Пушкино-2 (Кроссдок) Ozon ' => 16.25,
		'Софьино Московская обл., Раменский городской округ, территория Логистический технопарк Софьино, 2/1' => 16.25,
		'Жуковский Ozon' => 16.25,
		'Гривно Ozon' => 16.25,
		'Гривно (Кроссдок) Ozon' => 16.25,
		'Адыгея Ozon' => 15.0,
		'Невинномысск Невинномысск, ул. Приозерная, зд. 25, стр. 1, к. 1' => 15.0,
		'Новосемейкино Индустриальный парк Новосемейкино, городское поселение Новосемейкино, Красноярский р-н, Самарская обл.' => 18.75,
		'Волгоград Волгоград, Ангарская 149' => 18.75,
		'Сарапул Удмуртская Республика, г. Сарапул, Ижевский тракт, д. 22' => 22.5,
		'Санкт-Петербург (Шушары) Санкт-Петербург, Пушкинский район, посёлок Шушары, Московское шоссе, д. 153 к. 2' => 22.5,
		'Владимир Wildberries' => 18.75,
		'Воронеж Воронежская обл., Новоусманский р-н, Отрадненское сельское поселение' => 17.5,
		'Софиьно Яндекс Маркет' => 16.25,
		'Ростов-на-Дону Яндекс Маркет' => 12.5,
		'Ногинск Московская область, Богородский городской округ, рабочий поселок Обухово, территория Обухово-Парк, дом 2, строение 1' => 25.0,
		'Ростовская обл.,  Аксайский р-н, Большелогское с.п., тер. Промышленная зона, зд. 180б, стр.1' => 12.5,
    ];

		public static function table(Table $table): Table
		{
				return $table
						->columns(static::applyInlineEditingToColumns([
								Tables\Columns\TextColumn::make('id')
										->label('№ заявки')
										->sortable()
										->searchable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('send_date')
										->label('Дата отправки')
										->date('d.m.Y')
										->placeholder('—')
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('send_date') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('created_at')
										->label('Дата и время')
										->dateTime('d.m.Y H:i')
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								// Отправитель из таблицы agents
								Tables\Columns\TextColumn::make('agent.title')
										->label('Отправитель (ФИО/ИП/ООО)')
										->searchable()
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('agent_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								// Контактное лицо из таблицы agents
								Tables\Columns\TextColumn::make('agent.name')
										->label('Контактное лицо')
										->searchable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('agent_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								// Номер телефона из таблицы agents
								Tables\Columns\TextColumn::make('agent.phone')
										->label('Номер телефона')
										->searchable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('agent_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('delivery_date')
										->label('Дата поставки на РЦ')
										->formatStateUsing(function ($state) {
											if (blank($state) || $state === '—' || $state === '–') {
												return '—';
											}
											try {
												return Carbon::parse($state)->format('d.m.Y');
											} catch (\Throwable $e) {
												return '—';
											}
										})
										->extraAttributes([
												'style' => 'width:3.5rem;max-width:3.5rem;',
										])
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('delivery_date') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('distribution')
										->label('РЦ и адрес')
										->getStateUsing(fn (Order $record) => $record->distribution_label ?: '—')
										->limit(60)
										->tooltip(fn (Order $record) => blank($record->distribution_label) ? null : $record->distribution_label)
										->searchable(['distributor_id', 'distributor_center_id'])
										->sortable(query: fn (Builder $query, string $direction) => $query
												->orderBy('distributor_id', $direction)
												->orderBy('distributor_center_id', $direction))
										->color(fn (Order $record) => $record->hasChanged('distributor_id', 'distributor_center_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\IconColumn::make('individual')
										->label('Индивид.')
										->boolean()
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('individual') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('individual_cost')
										->label('Индивид стоимость')
										->money('RUB')
										->getStateUsing(fn (Order $record) => $record->individual ? static::calculateIndividualCost($record) : null)
										->default('—')
										->visible(fn () => in_array(optional(auth()->user())->role, ['manager', 'admin'], true))
										->toggleable(isToggledHiddenByDefault: false),
								
								// Груз
								Tables\Columns\TextColumn::make('cargo')
										->label('Груз')
										->formatStateUsing(fn ($state) => match($state) {
												'boxes' => 'Коробки',
												'pallets' => 'Палеты',
												default => $state
										})
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('cargo') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Кол-во палет
								Tables\Columns\TextColumn::make('pallets_count')
										->label('Кол-во палет')
										->numeric()
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('pallets_count') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Кол-во коробов
								Tables\Columns\TextColumn::make('boxes_count')
										->label('Общ. кол-во коробов')
										->numeric()
										->getStateUsing(fn (Order $record) => static::resolveDisplayValue($record, 'boxes_count'))
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_count', 'pallets_boxcount') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Объем коробов
								Tables\Columns\TextColumn::make('boxes_volume')
										->label('Объем коробов, м³')
										->numeric()
										->suffix(' м³')
										->getStateUsing(fn (Order $record) => static::resolveDisplayValue($record, 'boxes_volume'))
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_volume', 'pallets_volume') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->formatStateUsing(fn (TextColumn $column, $state): mixed => static::resolveDisplayValue(
												$column->getRecord(),
												'boxes_weight',
										))
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_weight', 'pallets_weight') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								

								// Палетирование (да/нет)
								Tables\Columns\IconColumn::make('has_palletizing')
										->label('Палетирование')
										->boolean()
										->getStateUsing(fn ($record) => $record->palletizing_count > 0)
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('palletizing_count') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Паллетирование кол-во
								Tables\Columns\TextColumn::make('palletizing_count')
										->label('Палетирование кол-во')
										->numeric()
										->sortable()
										->default(0)
										->color(fn (Order $record) => $record->hasChanged('palletizing_count') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Забор груза (да/нет)
								Tables\Columns\IconColumn::make('has_pickup')
										->label('Забор груза')
										->boolean()
										->getStateUsing(fn ($record) => $record->transfer_method === 'pick')
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('transfer_method') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Дата привоза клиентом
								Tables\Columns\TextColumn::make('transfer_method_receive_date')
										->label('Дата привоза клиентом')
										->formatStateUsing(function ($state) {
											if (blank($state) || $state === '—' || $state === '–') {
												return '—';
											}
											try {
												return Carbon::parse($state)->format('d.m.Y');
											} catch (\Throwable $e) {
												return '—';
											}
										})
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('transfer_method_receive_date') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
										
										// Дата забора груза
										Tables\Columns\TextColumn::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->formatStateUsing(function ($state) {
											if (blank($state) || $state === '—' || $state === '–') {
												return '—';
											}
											try {
												return Carbon::parse($state)->format('d.m.Y');
											} catch (\Throwable $e) {
												return '—';
											}
										})
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('transfer_method_pick_date') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
										
										// Адрес забора груза
										Tables\Columns\TextColumn::make('transfer_method_pick_address')
										->label('Адрес забора')
										->searchable()
										->limit(30)
										->tooltip(fn ($state) => $state)
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('transfer_method_pick_address') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('payment_method')
										->label('Способ оплаты')
										->formatStateUsing(fn ($state) => match($state) {
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
												default => $state
										})
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('payment_method') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								// Оплата за забор груза
								Tables\Columns\TextColumn::make('pick')
										->label('Стоимость забора')
										->money('RUB')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('pick') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
										
								Tables\Columns\TextColumn::make('delivery')
										->label('Стоимость доставки')
										->money('RUB')
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('delivery') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('additional')
										->label('Стоимость паллетирования')
										->money('RUB')
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('additional') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('total')
										->label('Предварительная сумма')
										->money('RUB')
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('total') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('cargo_comment')
										->label('Комментарий')
										->limit(30)
										->tooltip(fn ($state) => $state)
										->color(fn (Order $record) => $record->hasChanged('cargo_comment') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('agent.email')
										->label('Email')
										->searchable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('agent_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('agent.inn')
										->label('ИНН')
										->searchable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('agent_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('agent.ogrn')
										->label('ОГРН')
										->searchable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('agent_id') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
						]))
						->headerActions([
								Tables\Actions\Action::make('filterSendDate')
										->label('Дата отправки')
										->icon('heroicon-o-calendar-days')
										->color('primary')
										->modalHeading('Выберите дату отправки')
										->modalSubmitActionLabel('Применить')
										->form([
												Forms\Components\DatePicker::make('send_date')
														->label('Дата отправки')
														->displayFormat('d.m.Y')
														->native(false)
														->closeOnDateSelection()
														->placeholder('Выберите дату'),
										])
										->fillForm(function (Pages\ListOrders $livewire): array {
												$filters = $livewire->tableFilters ?? [];

												if (isset($filters['send_date_exact']['value'])) {
														return ['send_date' => $filters['send_date_exact']['value']];
												}

												return [];
										})
										->action(function (array $data, Pages\ListOrders $livewire): void {
												$filters = $livewire->tableFilters ?? [];

												unset(
													$filters['send_date_today'],
													$filters['send_date_set'],
													$filters['send_date_missing']
												);

												if (!empty($data['send_date'])) {
														$filters['send_date_exact'] = [
																'value' => Carbon::parse($data['send_date'])->toDateString(),
														];
												} else {
														unset($filters['send_date_exact']);
												}

												$livewire->tableFilters = empty($filters) ? null : $filters;
												$livewire->updatedTableFilters();
										}),
						])
						->filters([
								Filter::make('send_date_exact')
										->label('Дата отправки')
										->form([
												Forms\Components\DatePicker::make('value')
														->label('Дата отправки')
														->displayFormat('d.m.Y')
														->native(false)
														->closeOnDateSelection(),
										])
										->query(fn (Builder $query, array $data): Builder => $query->when(
												$data['value'] ?? null,
												fn (Builder $q, string $value) => $q->whereDate('send_date', '=', $value),
										))
										->indicateUsing(fn (array $data): array => isset($data['value']) && $data['value'] !== null
												? ['Дата отправки: ' . Carbon::parse($data['value'])->format('d.m.Y')]
												: []),
								Filter::make('advanced_rules')
										->label('Фильтр по правилам')
										->form([
												Forms\Components\Repeater::make('rules')
														->label('Правила')
														->schema([
																Forms\Components\Select::make('field')
																		->label('Поле')
																		->options(static::getRuleFilterFieldOptions())
																		->required()
																		->reactive()
																		->searchable(),
																Forms\Components\Select::make('operator')
																		->label('Условие')
																		->options(fn (Get $get): array => static::getRuleOperatorOptions($get('field')))
																		->required()
																		->reactive(),
																Forms\Components\TextInput::make('value')
																		->label('Значение')
																		->placeholder(fn (Get $get): ?string => static::getRuleValuePlaceholder($get('field')))
																		->helperText(fn (Get $get): ?string => static::getRuleValueHelperText($get('field')))
																		->visible(fn (Get $get): bool => static::ruleOperatorRequiresValue($get('operator')))
																		->required(fn (Get $get): bool => static::ruleOperatorRequiresValue($get('operator'))),
														])
														->addActionLabel('Добавить правило')
														->columnSpanFull()
														->columns(3)
														->collapsed()
														->defaultItems(1)
														->minItems(1)
														->maxItems(5),
										])
										->query(fn (Builder $query, array $data): Builder => static::applyRulesFilter($query, $data['rules'] ?? []))
										->indicateUsing(fn (array $state): array => static::getRuleFilterIndicators($state['rules'] ?? [])),
								SelectFilter::make('agent_id')
										->label('Отправитель')
										->relationship('agent', 'title')
										->searchable()
										->preload()
										->indicator('Отправитель'),
								Filter::make('delivery_date')
										->label('Дата поставки на РЦ')
										->form([
												Forms\Components\Fieldset::make('Дата поставки на РЦ')
														->schema([
																Forms\Components\DatePicker::make('from')
																		->label('С')
																		->displayFormat('d.m.Y'),
																Forms\Components\DatePicker::make('to')
																		->label('По')
																		->displayFormat('d.m.Y'),
														])
														->columns(2),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('delivery_date', '>=', $date))
														->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('delivery_date', '<=', $date));
										})
										->indicateUsing(function (array $data): array {
												$indicators = [];

												if ($data['from'] ?? null) {
														$indicators[] = 'Поставка с ' . Carbon::parse($data['from'])->format('d.m.Y');
												}

												if ($data['to'] ?? null) {
														$indicators[] = 'Поставка по ' . Carbon::parse($data['to'])->format('d.m.Y');
												}

												return $indicators;
										}),
								SelectFilter::make('distributor_center_id')
										->label('РЦ и адреса')
										->searchable()
										->preload()
										->options(fn () => Order::query()
												->select('distributor_center_id')
												->whereNotNull('distributor_center_id')
												->distinct()
												->orderBy('distributor_center_id')
												->pluck('distributor_center_id', 'distributor_center_id')
												->toArray())
										->indicator('РЦ и адрес'),
								SelectFilter::make('payment_method')
										->label('Способ оплаты')
										->options([
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
										])
										->indicator('Способ оплаты'),
								TernaryFilter::make('has_pickup')
										->label('Забор груза')
										->nullable()
										->placeholder('Все')
										->trueLabel('Да')
										->falseLabel('Нет')
										->queries(
												true: fn (Builder $query): Builder => $query->where('transfer_method', 'pick'),
												false: fn (Builder $query): Builder => $query->where('transfer_method', '!=', 'pick'),
										)
										->indicateUsing(fn (array $data): array => array_key_exists('value', $data) && $data['value'] !== null ? ['Забор: ' . ($data['value'] ? 'Да' : 'Нет')] : []),
								Filter::make('transfer_method_receive_date')
										->label('Дата привоза клиентом')
										->form([
												Forms\Components\Fieldset::make('Дата привоза клиентом')
														->schema([
																Forms\Components\DatePicker::make('from')->label('С')->displayFormat('d.m.Y'),
																Forms\Components\DatePicker::make('to')->label('По')->displayFormat('d.m.Y'),
														])
														->columns(2),
										])
										->query(fn (Builder $query, array $data): Builder => $query
												->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('transfer_method_receive_date', '>=', $date))
												->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('transfer_method_receive_date', '<=', $date)))
										->indicateUsing(fn (array $data): array => array_filter([
												isset($data['from']) ? 'Привоз с ' . Carbon::parse($data['from'])->format('d.m.Y') : null,
												isset($data['to']) ? 'Привоз по ' . Carbon::parse($data['to'])->format('d.m.Y') : null,
										])),
								Filter::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->form([
												Forms\Components\Fieldset::make('Дата забора груза')
														->schema([
																Forms\Components\DatePicker::make('from')->label('С')->displayFormat('d.m.Y'),
																Forms\Components\DatePicker::make('to')->label('По')->displayFormat('d.m.Y'),
														])
														->columns(2),
										])
										->query(fn (Builder $query, array $data): Builder => $query
												->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('transfer_method_pick_date', '>=', $date))
												->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('transfer_method_pick_date', '<=', $date)))
										->indicateUsing(fn (array $data): array => array_filter([
												isset($data['from']) ? 'Забор с ' . Carbon::parse($data['from'])->format('d.m.Y') : null,
												isset($data['to']) ? 'Забор по ' . Carbon::parse($data['to'])->format('d.m.Y') : null,
										])),
								Filter::make('transfer_method_pick_address')
										->label('Адрес забора')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Содержит')
														->placeholder('Введите часть адреса'),
										])
										->query(fn (Builder $query, array $data): Builder => $query->when(
												$data['value'] ?? null,
												fn (Builder $q, string $value) => $q->where('transfer_method_pick_address', 'like', '%' . $value . '%'),
										))
										->indicateUsing(fn (array $data): array => $data['value'] ?? null ? ['Адрес забора: ' . $data['value']] : []),
								
						])
						->actions([
								Tables\Actions\ViewAction::make()
										->modalHeading('Информация о заявке')
										->modalWidth('full')
										->infolist(fn (Infolists\Infolist $infolist) => static::infolist($infolist)),
								Tables\Actions\Action::make('download')
										->label('Скачать')
										->icon('heroicon-o-arrow-down-tray')
										->url(fn (Order $record) => route('filament.resources.orders.export-single', $record))
										->openUrlInNewTab(),
								Tables\Actions\EditAction::make(),
						])
						->bulkActions([
								Tables\Actions\BulkAction::make('exportSelected')
										->label('Экспорт')
										->icon('heroicon-o-arrow-down-tray')
										->color('primary')
										->action(fn (Collection $records, Pages\ListOrders $livewire) => $livewire->exportSelectedRecords($records))
										->deselectRecordsAfterCompletion(),
								Tables\Actions\BulkAction::make('setSendDate')
										->label('Привязать дату')
										->icon('heroicon-o-calendar')
										->form([
												Forms\Components\DatePicker::make('send_date')
														->label('Дата отправки')
														->displayFormat('d.m.Y')
														->required(),
										])
										->action(function (Collection $records, array $data, Pages\ListOrders $livewire): void {
												$date = Carbon::parse($data['send_date'])->toDateString();

												$records->each(function (Order $order) use ($date): void {
														$order->send_date = $date;
														$order->save();
												});

												$livewire->dispatch('refresh');
										})
										->deselectRecordsAfterCompletion(),
								Tables\Actions\DeleteBulkAction::make(),
						])
						->persistFiltersInSession()
						->persistSearchInSession()
						->persistColumnSearchesInSession()
						->defaultSort('created_at', 'desc')
						->recordUrl(null)
						->recordAction(null)
						->paginated()
						->paginationPageOptions([25, 50, 100, 200])
						->defaultPaginationPageOption(25);
		}

		protected static function getRuleFilterFields(): array
		{
				return [
						'id' => [
								'label' => '№ заявки',
								'column' => 'id',
								'type' => 'number',
						],
						'send_date' => [
								'label' => 'Дата отправки',
								'column' => 'send_date',
								'type' => 'date',
						],
						'delivery_date' => [
								'label' => 'Дата поставки на РЦ',
								'column' => 'delivery_date',
								'type' => 'date',
						],
						'created_at' => [
								'label' => 'Дата создания',
								'column' => 'created_at',
								'type' => 'datetime',
						],
						'payment_method' => [
								'label' => 'Способ оплаты',
								'column' => 'payment_method',
								'type' => 'enum',
								'options' => [
										'cash' => 'Наличные',
										'bill' => 'Безналичный',
								],
						],
						'transfer_method' => [
								'label' => 'Способ передачи',
								'column' => 'transfer_method',
								'type' => 'enum',
								'options' => [
										'receive' => 'Привоз клиентом',
										'pick' => 'Забор грузополучателем',
								],
						],
						'cargo' => [
								'label' => 'Тип груза',
								'column' => 'cargo',
								'type' => 'enum',
								'options' => [
										'boxes' => 'Коробки',
										'pallets' => 'Палеты',
								],
						],
						'individual' => [
								'label' => 'Индивидуальный расчет',
								'column' => 'individual',
								'type' => 'boolean',
						],
						'pallets_count' => [
								'label' => 'Кол-во палет',
								'column' => 'pallets_count',
								'type' => 'number',
						],
						'boxes_count' => [
								'label' => 'Кол-во коробов',
								'column' => 'boxes_count',
								'type' => 'number',
						],
						'agent_title' => [
								'label' => 'Отправитель (ФИО/ИП/ООО)',
								'column' => 'agent.title',
								'type' => 'string',
						],
						'agent_name' => [
								'label' => 'Контактное лицо',
								'column' => 'agent.name',
								'type' => 'string',
						],
						'agent_phone' => [
								'label' => 'Телефон отправителя',
								'column' => 'agent.phone',
								'type' => 'string',
						],
						'agent_email' => [
								'label' => 'Email отправителя',
								'column' => 'agent.email',
								'type' => 'string',
						],
				];
		}

		protected static function getRuleFilterFieldOptions(): array
		{
				return collect(static::getRuleFilterFields())
						->mapWithKeys(fn (array $config, string $key) => [$key => $config['label']])
						->all();
		}

		protected static function getRuleOperatorOptions(?string $field): array
		{
				return match (static::getRuleFieldType($field)) {
						'number', 'date', 'datetime' => [
								'equals' => 'Равно',
								'not_equals' => 'Не равно',
								'gt' => 'Больше',
								'gte' => 'Больше или равно',
								'lt' => 'Меньше',
								'lte' => 'Меньше или равно',
								'is_empty' => 'Пусто',
								'is_not_empty' => 'Не пусто',
						],
						'enum', 'boolean' => [
								'equals' => 'Равно',
								'not_equals' => 'Не равно',
						],
						default => [
								'contains' => 'Содержит',
								'not_contains' => 'Не содержит',
								'equals' => 'Равно',
								'not_equals' => 'Не равно',
								'starts_with' => 'Начинается с',
								'ends_with' => 'Заканчивается на',
								'is_empty' => 'Пусто',
								'is_not_empty' => 'Не пусто',
						],
				};
		}

		protected static function getRuleFieldType(?string $field): ?string
		{
				return static::getRuleFilterFields()[$field]['type'] ?? null;
		}

		protected static function ruleOperatorRequiresValue(?string $operator): bool
		{
				return ! in_array($operator, ['is_empty', 'is_not_empty'], true);
		}

		protected static function getRuleValuePlaceholder(?string $field): ?string
		{
				return match (static::getRuleFieldType($field)) {
						'date' => 'гггг-мм-дд',
						'datetime' => 'гггг-мм-дд чч:мм',
						'number' => 'Введите число',
						default => null,
				};
		}

		protected static function getRuleValueHelperText(?string $field): ?string
		{
				$config = static::getRuleFilterFields()[$field] ?? null;

				if (! $config) {
						return null;
				}

				return match ($config['type']) {
						'enum' => 'Доступные значения: ' . implode(', ', $config['options'] ?? []),
						'boolean' => 'Используйте 1/0, true/false или да/нет',
						default => null,
				};
		}

		protected static function applyRulesFilter(Builder $query, array $rules): Builder
		{
				foreach ($rules as $rule) {
						$query = static::applySingleRule($query, $rule);
				}

				return $query;
		}

		protected static function applySingleRule(Builder $query, array $rule): Builder
		{
				$fieldKey = $rule['field'] ?? null;
				$operator = $rule['operator'] ?? null;
				$value = $rule['value'] ?? null;

				if (blank($fieldKey) || blank($operator)) {
						return $query;
				}

				$fields = static::getRuleFilterFields();
				$field = $fields[$fieldKey] ?? null;

				if (! $field) {
						return $query;
				}

				if (static::ruleOperatorRequiresValue($operator)) {
						$value = static::normalizeRuleValue($field, $operator, $value);

						if ($value === null || $value === '') {
								return $query;
						}
				}

				$column = $field['column'];
				$type = $field['type'];

				if (str_contains($column, '.')) {
						[$relation, $relatedColumn] = explode('.', $column, 2);

						return $query->whereHas($relation, function (Builder $relationQuery) use ($relatedColumn, $type, $operator, $value): Builder {
								return static::applyRuleToColumn($relationQuery, $relatedColumn, $type, $operator, $value);
						});
				}

				return static::applyRuleToColumn($query, $column, $type, $operator, $value);
		}

		protected static function applyRuleToColumn(Builder $query, string $column, string $type, string $operator, mixed $value): Builder
		{
				return match ($operator) {
						'contains' => $query->where($column, 'like', '%' . static::escapeLike((string) $value) . '%'),
						'not_contains' => $query->where(function (Builder $subQuery) use ($column, $value): Builder {
								return $subQuery
										->where($column, 'not like', '%' . static::escapeLike((string) $value) . '%')
										->orWhereNull($column);
						}),
						'starts_with' => $query->where($column, 'like', static::escapeLike((string) $value) . '%'),
						'ends_with' => $query->where($column, 'like', '%' . static::escapeLike((string) $value)),
						'equals' => $query->where($column, '=', $value),
						'not_equals' => $query->where($column, '!=', $value),
						'gt' => $query->where($column, '>', $value),
						'gte' => $query->where($column, '>=', $value),
						'lt' => $query->where($column, '<', $value),
						'lte' => $query->where($column, '<=', $value),
						'is_empty' => $query->where(function (Builder $subQuery) use ($column): Builder {
								return $subQuery
										->whereNull($column)
										->orWhere($column, '=', '');
						}),
						'is_not_empty' => $query->where(function (Builder $subQuery) use ($column): Builder {
								return $subQuery
										->whereNotNull($column)
										->where($column, '!=', '');
						}),
						default => $query,
				};
		}

		protected static function normalizeRuleValue(array $field, string $operator, ?string $value): mixed
		{
				if ($value === null) {
						return null;
				}

				$type = $field['type'] ?? 'string';
				$trimmed = is_string($value) ? trim($value) : $value;

				return match ($type) {
						'number' => is_numeric($trimmed) ? $trimmed + 0 : null,
						'date' => blank($trimmed) ? null : static::tryParseDate($trimmed)?->toDateString(),
						'datetime' => blank($trimmed) ? null : static::tryParseDate($trimmed)?->format('Y-m-d H:i:s'),
						'enum' => static::normalizeEnumValue($field['options'] ?? [], $trimmed),
						'boolean' => static::normalizeBooleanValue($trimmed),
						default => $trimmed,
				};
		}

		protected static function normalizeBooleanValue(?string $value): ?bool
		{
				if ($value === null) {
						return null;
				}

				$normalized = Str::lower(trim((string) $value));

				return match (true) {
						in_array($normalized, ['1', 'true', 'yes', 'да'], true) => true,
						in_array($normalized, ['0', 'false', 'no', 'нет'], true) => false,
						default => null,
				};
		}

		protected static function tryParseDate(string $value): ?Carbon
		{
				try {
						return Carbon::parse($value);
				} catch (\Throwable $e) {
						return null;
				}
		}

		protected static function normalizeEnumValue(array $options, string $value): ?string
		{
				foreach ($options as $optionValue => $label) {
						if ($value === (string) $optionValue) {
								return (string) $optionValue;
						}

						if (Str::lower($value) === Str::lower((string) $label)) {
								return (string) $optionValue;
						}
				}

				return null;
		}

		protected static function escapeLike(string $value): string
		{
				return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
		}

		protected static function getRuleFilterIndicators(array $rules): array
		{
				$validRules = collect($rules)
						->filter(fn (array $rule) => filled($rule['field'] ?? null) && filled($rule['operator'] ?? null))
						->count();

				if ($validRules === 0) {
						return [];
				}

				return [
						Indicator::make('Правил: ' . $validRules),
				];
		}

		protected static function buildBoxPalletExpression(string $attribute, string $palletField, string $boxField): string
		{
			$parts = explode('.', $attribute);
			$table = count($parts) > 1 ? $parts[0] : 'orders';

			return "CASE WHEN {$table}.pallets_count > 0 THEN COALESCE({$table}.{$palletField}, {$table}.{$boxField}) ELSE {$table}.{$boxField} END";
		}


    public static function form(Form $form): Form
		{
				return $form->schema([
						Forms\Components\Section::make('Основная информация')
								->schema([
										Forms\Components\TextInput::make('id')
												->label('№ заявки')
												->disabled(),
										Forms\Components\Select::make('user_id')
												->label('Пользователь')
												->relationship('user', 'name')
												->searchable()
												->required(),
										Forms\Components\Select::make('agent_id')
												->label('Отправитель')
												->relationship('agent', 'title')
												->searchable()
												->required(),
										Forms\Components\Select::make('transfer_method')
												->label('Способ передачи')
												->options([
														'receive' => 'Привоз клиентом',
														'pick'    => 'Забор грузополучателем',
												])
												->required(),
										Forms\Components\Select::make('payment_method')
												->label('Способ оплаты')
												->options([
														'cash' => 'Наличные',
														'bill' => 'Безналичный',
												])
												->required(),
										Forms\Components\Select::make('payment_method_pick')
												->label('Оплата за забор')
												->options([
														'cash' => 'Наличные',
														'bill' => 'Безналичный',
												]),
										Forms\Components\Toggle::make('individual')
												->label('Индивидуальный расчет'),
										Forms\Components\DateTimePicker::make('delivery_date')
												->label('Дата поставки')
												->required(),
										Forms\Components\DateTimePicker::make('post_date')
												->label('Дата публикации'),
								])
								->columns(3),

						Forms\Components\Section::make('Локации')
								->schema([
										Forms\Components\TextInput::make('warehouse_id')
												->label('Склад')
												->required(),
										Forms\Components\TextInput::make('distributor_id')
												->label('Дистрибьютор')
												->required(),
										Forms\Components\TextInput::make('distributor_center_id')
												->label('Адрес РЦ')
												->required(),
								])
								->columns(3),

						Forms\Components\Section::make('Груз')
								->schema([
										Forms\Components\Select::make('cargo')
												->label('Тип груза')
												->options([
														'boxes'   => 'Коробки',
														'pallets' => 'Палеты',
												])
												->required(),
										Forms\Components\TextInput::make('cargo_type')
												->label('Описание груза'),
										Forms\Components\TextInput::make('boxes_count')
												->label('Кол-во коробов')
												->numeric(),
										Forms\Components\TextInput::make('boxes_weight')
												->label('Вес коробов, кг')
												->numeric(),
										Forms\Components\TextInput::make('boxes_volume')
												->label('Объем коробов, м³')
												->numeric(),
										Forms\Components\TextInput::make('pallets_count')
												->label('Кол-во палет')
												->numeric(),
										Forms\Components\TextInput::make('pallets_boxcount')
												->label('Коробов в палете')
												->numeric(),
										Forms\Components\TextInput::make('pallets_weight')
												->label('Вес палет, кг')
												->numeric(),
										Forms\Components\TextInput::make('pallets_volume')
												->label('Объем палет, м³')
												->numeric(),
										Forms\Components\Select::make('palletizing_type')
												->label('Тип палетирования')
												->options([
														'single' => 'Палетирование',
														'pallet' => 'Поддон + палетирование',
												]),
										Forms\Components\TextInput::make('palletizing_count')
												->label('Кол-во палетирования')
												->numeric(),
										Forms\Components\Textarea::make('cargo_comment')
												->label('Комментарий')
												->columnSpanFull(),
								])
								->columns(3),

						Forms\Components\Section::make('Получение/забор')
								->schema([
										Forms\Components\DateTimePicker::make('transfer_method_receive_date')
												->label('Дата привоза клиентом'),
										Forms\Components\DateTimePicker::make('transfer_method_pick_date')
												->label('Дата забора')
												->nullable()
												->dehydrated(function ($state, ?Order $record) {
														if (! $record) {
																return filled($state);
														}

														if (blank($state)) {
																return ! blank($record->transfer_method_pick_date);
														}

														try {
																$incoming = Carbon::parse($state);
														} catch (\Throwable $e) {
																return true;
														}

														$current = $record->transfer_method_pick_date
																? Carbon::parse($record->transfer_method_pick_date)
																: null;

														return ! $current || ! $incoming->equalTo($current);
												})
												->dehydrateStateUsing(fn ($state) => blank($state) ? null : Carbon::parse($state)->format('Y-m-d H:i:s')),
										Forms\Components\Textarea::make('transfer_method_pick_address')
												->label('Адрес забора')
												->columnSpanFull(),
								])
								->columns(2),

						Forms\Components\Section::make('Стоимость')
								->schema([
										Forms\Components\TextInput::make('pick')
												->label('Забор, ₽')
												->numeric()
												->prefix('₽'),
										Forms\Components\TextInput::make('delivery')
												->label('Доставка, ₽')
												->numeric()
												->prefix('₽'),
										Forms\Components\TextInput::make('additional')
												->label('Палетирование, ₽')
												->numeric()
												->prefix('₽'),
										Forms\Components\TextInput::make('total')
												->label('Итого, ₽')
												->numeric()
												->prefix('₽'),
										Forms\Components\TextInput::make('cash_accepted')
												->label('Принято, ₽')
												->numeric()
												->prefix('₽'),
								])
								->columns(5),
				]);
		}


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

		public static function getInlineEditableFields(): array
		{
				return static::$inlineEditableFields;
		}

		public static function applyInlineEditingToColumns(array $columns): array
		{
				return array_map(function ($column) {
						if (! $column instanceof Column) {
								return $column;
						}

						$field = $column->getName();

						if (! static::isInlineEditableField($field)) {
								return $column;
						}

						$label = $column->getLabel();

						$column->extraCellAttributes(function (Order $record) use ($field, $label): array {
								$targetField = static::resolveInlineFieldTarget($record, $field);
								$value = static::resolveInlineFieldValue($record, $field);

								if ($value instanceof \DateTimeInterface) {
										$value = $value->format('Y-m-d H:i:s');
								} elseif (is_array($value)) {
										$value = json_encode($value, JSON_UNESCAPED_UNICODE);
								} elseif ($value === null) {
										$value = '';
								}

								return [
										'data-inline-editable' => '1',
										'data-inline-field' => $targetField,
										'data-inline-label' => is_string($label) ? $label : (string) $label,
										'data-inline-record' => (string) $record->getKey(),
										'data-inline-value' => (string) $value,
										'class' => 'fi-inline-editable-cell',
								];
						});

						return $column;
				}, $columns);
		}

		protected static function isInlineEditableField(?string $field): bool
		{
				if (blank($field)) {
						return false;
				}

				return in_array($field, static::$inlineEditableFields, true);
		}

		protected static function fallbackValue(mixed $primary, mixed $fallback): mixed
		{
				if (static::isEmptyValue($primary)) {
						return static::isEmptyValue($fallback) ? null : $fallback;
				}

				return $primary;
		}

		protected static function fallbackWeightValue(mixed $primary, mixed $fallback): mixed
		{
				if ($primary === null) {
						return static::isEmptyValue($fallback) ? null : $fallback;
				}

				if (is_numeric($primary) && (float) $primary === 0.0) {
						return static::isEmptyValue($fallback) ? 0 : $fallback;
				}

				return $primary;
		}

		protected static function isEmptyValue(mixed $value): bool
		{
				if ($value === null) {
						return true;
				}

				if (is_string($value)) {
						$value = trim($value);

						if ($value === '') {
								return true;
						}
				}

				if (is_numeric($value)) {
						return (float) $value === 0.0;
				}

				return false;
		}

		protected static function resolveInlineFieldValue(Order $record, string $field): mixed
		{
				return static::resolveDisplayValue($record, $field);
		}

		protected static function resolveInlineFieldTarget(Order $record, string $field): string
		{
				$hasPallets = static::recordHasPallets($record);

				return match ($field) {
						'boxes_count' => $hasPallets ? 'pallets_boxcount' : 'boxes_count',
						'boxes_volume' => $hasPallets ? 'pallets_volume' : 'boxes_volume',
						'boxes_weight' => $hasPallets ? 'pallets_weight' : 'boxes_weight',
						'distribution' => 'distribution_edit',
						default => $field,
				};
		}

		protected static function resolveDisplayValue(Order $record, string $field): mixed
		{
				$hasPallets = static::recordHasPallets($record);

				return match ($field) {
						'boxes_count' => $hasPallets
								? ($record->pallets_boxcount ?? $record->boxes_count)
								: $record->boxes_count,
						'boxes_volume' => $hasPallets
								? ($record->pallets_volume ?? $record->boxes_volume)
								: $record->boxes_volume,
						'boxes_weight' => $hasPallets
								? $record->pallets_weight
								: $record->boxes_weight,
						'distribution' => static::formatDistributionForEdit($record),
						default => data_get($record, $field),
				};
		}

		protected static function recordHasPallets(Order $record): bool
		{
				$count = $record->pallets_count;

				return $count !== null && (float) $count > 0;
		}

		protected static function formatDistributionForEdit(Order $record): string
		{
				// Если есть distribution_label, разбиваем его по " - "
				if ($record->distribution_label && str_contains($record->distribution_label, ' - ')) {
						$parts = explode(' - ', $record->distribution_label, 2);
						return trim($parts[0] ?? '') . '|' . trim($parts[1] ?? '');
				}

				// Иначе используем прямые значения полей
				$distributorId = $record->distributor_id ?? '';
				$distributorCenterId = $record->distributor_center_id ?? '';

				return $distributorId . '|' . $distributorCenterId;
		}

		protected static function calculateIndividualCost(Order $record): ?float
		{
				if (! $record->individual) {
						return null;
				}

				$palletsCount = static::toFloat($record->pallets_count);
				$volume = static::toFloat(static::resolveDisplayValue($record, 'boxes_volume'));
				$weight = static::toFloat(static::resolveDisplayValue($record, 'boxes_weight'));

				if ($palletsCount <= 0 && $volume === 0.0 && $weight === 0.0) {
						return null;
				}

				$tariff = static::resolveIndividualTariff($record, $palletsCount, $volume, $weight);

				if ($tariff === null) {
						return null;
				}

				$address = static::resolveDistributionAddress($record);

				if ($address !== null && static::isSimferopolSmallVolume($address, $volume)) {
						return 500.0;
				}

				if ($palletsCount > 0) {
						$base = $tariff * $palletsCount;

						if ($weight > 0 && ($weight / $palletsCount) > 400) {
								$extraWeight = max(0.0, $weight - (400 * $palletsCount));
								$extraRate = static::resolveOverweightRate($record);

								return $base + ($extraRate * $extraWeight);
						}

						return $base;
				}

				if ($volume <= 0.0) {
						return null;
				}

				if ($volume <= 0.1 && $weight <= 30) {
						return ($tariff * 0.1) + 200;
				}

				if ($weight > 0 && ($weight / $volume) > 300) {
						return $weight * $tariff;
				}

				return $volume * $tariff;
		}

		protected static function resolveIndividualTariff(Order $record, float $palletsCount, float $volume, float $weight): ?float
		{
				$key = static::resolveFullDistributionKey($record);

				if ($key === null) {
						return null;
				}

				if ($palletsCount > 0) {
						return static::mapLookup(static::$individualBaseTariffs, $key);
				}

				if ($volume > 0 && $weight > 0 && ($weight / $volume) > 300) {
						$rate = static::mapLookup(static::$individualWeightTariffs, $key);

						return $rate;
				}

				return static::mapLookup(static::$individualBaseTariffs, $key);
		}

		protected static function resolveOverweightRate(Order $record): float
		{
				$address = static::resolveDistributionAddress($record);

				if ($address === null) {
						return 0.0;
				}

				return static::mapLookup(static::$individualOverweightRates, $address) ?? 0.0;
		}

		protected static function resolveFullDistributionKey(Order $record): ?string
		{
				$marketplace = static::resolveMarketplace($record);
				$city = static::extractCity(static::resolveDistributionAddress($record));

				if ($marketplace === null || $city === null) {
						return null;
				}

				return static::normalizeString($marketplace . ' - ' . $city);
		}

		protected static function resolveMarketplace(Order $record): ?string
		{
				$label = static::normalizeString($record->distribution_label);

				if ($label !== null && str_contains($label, ' - ')) {
						[$marketplace,] = explode(' - ', $label, 2);

						return static::normalizeString($marketplace);
				}

				return static::normalizeString($record->distributor_id);
		}

		protected static function resolveDistributionAddress(Order $record): ?string
		{
				$label = static::normalizeString($record->distribution_label);

				if ($label !== null && str_contains($label, ' - ')) {
						[, $address] = explode(' - ', $label, 2);

						return static::normalizeString($address);
				}

				return static::normalizeString($record->distributor_center_id);
		}

		protected static function extractCity(?string $address): ?string
		{
				if ($address === null) {
						return null;
				}

				if (! preg_match('/^\S+/u', $address, $matches)) {
						return null;
				}

				return static::normalizeString($matches[0]);
		}

		protected static function isSimferopolSmallVolume(string $address, float $volume): bool
		{
				$target = static::normalizeString('Симферополь (Молодежное) пгт. Молодежное, Московское шоссе, 11');

				return static::normalizeString($address) === $target
						&& $volume >= 0.05
						&& $volume <= 0.3;
		}

		protected static function mapLookup(array $map, ?string $key): ?float
		{
				$normalized = static::normalizeString($key);

				if ($normalized === null) {
						return null;
				}

				foreach ($map as $candidate => $value) {
						if (static::normalizeString($candidate) === $normalized) {
								return (float) $value;
						}
				}

				return null;
		}

		protected static function normalizeString(?string $value): ?string
		{
				if ($value === null) {
						return null;
				}

				$value = trim($value);

				if ($value === '') {
						return null;
				}

				return (string) Str::of($value)->replaceMatches('/\s+/u', ' ')->trim();
		}

		protected static function toFloat(mixed $value): float
		{
				if (is_string($value)) {
						$value = str_replace(',', '.', $value);
				}

				return is_numeric($value) ? (float) $value : 0.0;
		}

		protected static function ensureOrderModel(mixed $record): Order
		{
				if ($record instanceof Order) {
						return $record;
				}

				$model = new Order();
				$model->forceFill((array) $record);

				return $model;
		}

		public static function infolist(Infolist $infolist): Infolist
		{
				return $infolist
						->schema([
							Infolists\Components\Grid::make(3)
									->schema([
											Infolists\Components\Section::make('Основное')
										->schema([
												Infolists\Components\TextEntry::make('id')
															->label('№ заявки')
															->size('sm'),
												Infolists\Components\TextEntry::make('created_at')
															->label('Создана')
															->dateTime('d.m.Y H:i')
															->size('sm'),
												Infolists\Components\TextEntry::make('agent.title')
														->label('Отправитель')
															->size('sm')
															->columnSpan(2)
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('agent.name')
														->label('Контактное лицо')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->agent?->name))
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('agent.phone')
															->label('Телефон')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->agent?->phone))
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('agent.email')
														->label('Email')
															->size('sm')
															->columnSpan(2)
															->hidden(fn (Order $record) => blank($record->agent?->email))
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
										])
												->columns(2)
												->compact()
												->columnSpan(1),
								
											Infolists\Components\Section::make('Доставка')
										->schema([
												Infolists\Components\TextEntry::make('delivery_date')
															->label('Поставка на РЦ')
														->date('d.m.Y')
															->size('sm')
														->extraAttributes(fn (Order $record) => $record->hasChanged('delivery_date') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('send_date')
															->label('Отправка')
															->date('d.m.Y')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->send_date))
															->extraAttributes(fn (Order $record) => $record->hasChanged('send_date') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('distribution')
															->label('РЦ и адрес')
															->state(fn (Order $record) => $record->distribution_label ?: '—')
															->size('sm')
															->columnSpan(2)
															->extraAttributes(fn (Order $record) => $record->hasChanged('distributor_id', 'distributor_center_id') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('warehouse_id')
														->label('Склад')
															->size('sm')
															->columnSpan(2)
														->extraAttributes(fn (Order $record) => $record->hasChanged('warehouse_id') ? ['class' => 'text-orange-500'] : []),
										])
												->columns(2)
												->compact()
												->columnSpan(1),
								
								Infolists\Components\Section::make('Груз')
										->schema([
												Infolists\Components\TextEntry::make('cargo')
															->label('Тип')
															->formatStateUsing(fn ($state) => match ($state) {
																'boxes' => 'Коробки',
																'pallets' => 'Палеты',
																default => $state,
														})
															->size('sm')
														->extraAttributes(fn (Order $record) => $record->hasChanged('cargo') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('boxes_count')
															->label('Коробов')
														->state(fn (Order $record) => static::resolveDisplayValue($record, 'boxes_count'))
															->size('sm')
															->hidden(fn (Order $record) => blank(static::resolveDisplayValue($record, 'boxes_count')))
														->extraAttributes(fn (Order $record) => $record->hasChanged('boxes_count') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('boxes_weight')
														->label('Вес коробов, кг')
														->suffix(' кг')
														->formatStateUsing(fn (Infolists\Components\TextEntry $entry, $state) => static::resolveDisplayValue(
																$entry->getRecord(),
																'boxes_weight',
														))
															->size('sm')
															->hidden(fn (Order $record) => blank(static::resolveDisplayValue($record, 'boxes_weight')))
														->extraAttributes(fn (Order $record) => $record->hasChanged('boxes_weight') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('boxes_volume')
														->label('Объем коробов, м³')
														->suffix(' м³')
														->state(fn (Order $record) => static::resolveDisplayValue($record, 'boxes_volume'))
															->size('sm')
															->hidden(fn (Order $record) => blank(static::resolveDisplayValue($record, 'boxes_volume')))
														->extraAttributes(fn (Order $record) => $record->hasChanged('boxes_volume') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('pallets_count')
															->label('Палет')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->pallets_count))
															->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_count') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('pallets_weight')
															->label('Вес палет, кг')
															->suffix(' кг')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->pallets_weight))
															->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_weight') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('pallets_volume')
															->label('Объем палет, м³')
															->suffix(' м³')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->pallets_volume))
															->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_volume') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('palletizing_count')
															->label('Палетирование, шт')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->palletizing_count))
															->extraAttributes(fn (Order $record) => $record->hasChanged('palletizing_count') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('cargo_comment')
														->label('Комментарий')
															->size('sm')
															->columnSpan(2)
															->hidden(fn (Order $record) => blank($record->cargo_comment))
														->extraAttributes(fn (Order $record) => $record->hasChanged('cargo_comment') ? ['class' => 'text-orange-500'] : []),
										])
												->columns(2)
												->compact()
												->columnSpan(1),
								
								Infolists\Components\Section::make('Стоимость')
										->schema([
												Infolists\Components\TextEntry::make('payment_method')
														->label('Способ оплаты')
															->formatStateUsing(fn ($state) => match ($state) {
																'cash' => 'Наличные',
																'bill' => 'Безналичный',
																default => $state,
														})
															->size('sm')
														->extraAttributes(fn (Order $record) => $record->hasChanged('payment_method') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\IconEntry::make('individual')
														->label('Индивидуальный расчет')
														->boolean()
															->size('sm')
														->extraAttributes(fn (Order $record) => $record->hasChanged('individual') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('pick')
															->label('Забор, ₽')
														->money('RUB')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->pick))
														->extraAttributes(fn (Order $record) => $record->hasChanged('pick') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('delivery')
															->label('Доставка, ₽')
														->money('RUB')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->delivery))
														->extraAttributes(fn (Order $record) => $record->hasChanged('delivery') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('additional')
															->label('Палетирование, ₽')
														->money('RUB')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->additional))
														->extraAttributes(fn (Order $record) => $record->hasChanged('additional') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('total')
															->label('Итого, ₽')
														->money('RUB')
														->weight('bold')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->total))
														->extraAttributes(fn (Order $record) => $record->hasChanged('total') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('cash_accepted')
															->label('Принято, ₽')
														->money('RUB')
														->weight('bold')
															->size('sm')
															->hidden(fn (Order $record) => $record->cash_accepted === null)
														->extraAttributes(fn (Order $record) => $record->hasChanged('cash_accepted') ? ['class' => 'text-orange-500'] : []),
										])
												->columns(2)
												->compact()
												->columnSpan(1),
								
								Infolists\Components\Section::make('Забор груза')
										->schema([
												Infolists\Components\TextEntry::make('transfer_method')
														->label('Способ передачи')
															->formatStateUsing(fn ($state) => match ($state) {
																'pick' => 'Забор',
																'receive' => 'Привоз клиентом',
																default => $state,
														})
															->size('sm')
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('transfer_method_pick_date')
															->label('Дата забора')
														->date('d.m.Y')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->transfer_method_pick_date))
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method_pick_date') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('transfer_method_receive_date')
															->label('Дата привоза')
														->date('d.m.Y')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->transfer_method_receive_date))
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method_receive_date') ? ['class' => 'text-orange-500'] : []),
														Infolists\Components\TextEntry::make('transfer_method_pick_address')
															->label('Адрес забора')
															->size('sm')
															->columnSpan(2)
															->hidden(fn (Order $record) => blank($record->transfer_method_pick_address))
															->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method_pick_address') ? ['class' => 'text-orange-500'] : []),
												])
												->columns(2)
												->compact()
												->columnSpan(1),
								
								Infolists\Components\Section::make('Реквизиты')
										->schema([
												Infolists\Components\TextEntry::make('agent.inn')
														->label('ИНН')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->agent?->inn))
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('agent.ogrn')
														->label('ОГРН')
															->size('sm')
															->hidden(fn (Order $record) => blank($record->agent?->ogrn))
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												Infolists\Components\TextEntry::make('agent.address')
														->label('Адрес')
															->size('sm')
															->columnSpan(2)
															->hidden(fn (Order $record) => blank($record->agent?->address))
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(2)
												->compact()
												->columnSpan(1),
										])
						]);
		}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'cash-statement' => Pages\CashStatement::route('/cash-statement'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        $items = parent::getNavigationItems();

        $items[] = NavigationItem::make('Ведомость по наличным')
            ->url(static::getUrl('cash-statement'))
            ->group(static::$navigationGroup ?? 'Отчеты')
            ->icon('heroicon-o-banknotes')
            ->sort((static::$navigationSort ?? 0) + 1);

        return $items;
    }

    public static function getSummaryDisplayValue(Order $record, string $field): mixed
    {
        return static::resolveDisplayValue($record, $field);
    }
}
