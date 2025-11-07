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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

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
										->date('d.m.Y')
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
								
								Tables\Columns\IconColumn::make('individual')
										->label('Индивид.')
										->boolean()
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('individual') ? 'warning' : null)
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
										->getStateUsing(fn (Order $record) => static::fallbackValue(
												$record->boxes_count,
												$record->pallets_count
										))
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_count') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Объем коробов
								Tables\Columns\TextColumn::make('boxes_volume')
										->label('Объем коробов, м³')
										->numeric()
										->suffix(' м³')
										->getStateUsing(fn (Order $record) => static::fallbackValue(
												$record->boxes_volume,
												$record->pallets_volume
										))
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_volume') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->getStateUsing(fn (Order $record) => static::fallbackValue(
												$record->boxes_weight,
												$record->pallets_weight
										))
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_weight') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_weight') ? 'warning' : null)
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
										->date('d.m.Y H:i')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('transfer_method_receive_date') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

										
										// Дата забора груза
										Tables\Columns\TextColumn::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->date('d.m.Y H:i')
										->sortable()
										->default('—')
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
								// Оплата за забор груза
								Tables\Columns\TextColumn::make('pick')
										->label('Оплата за забор')
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
								Tables\Actions\Action::make('toggleSendDateToday')
										->label('Отправки сегодня')
										->icon('heroicon-o-calendar-days')
										->color('primary')
										->action(function (Pages\ListOrders $livewire): void {
												$filters = $livewire->tableFilters ?? [];
												$isActive = $filters['send_date_today']['isActive'] ?? false;

												unset($filters['send_date_set'], $filters['send_date_missing']);

												if ($isActive) {
														unset($filters['send_date_today']);
												} else {
														$filters['send_date_today'] = ['isActive' => true];
												}

												$livewire->tableFilters = empty($filters) ? null : $filters;
												$livewire->updatedTableFilters();
										}),
						])
						->filters([
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
								
								
						])
						->actions([
								Tables\Actions\ViewAction::make()
										->modalHeading('Информация о заявке')
										->modalWidth('7xl') // Большая ширина модального окна
										->infolist(fn (Infolists\Infolist $infolist) => static::infolist($infolist)),
								Tables\Actions\EditAction::make(),
						])
						->bulkActions([
								Tables\Actions\BulkActionGroup::make([
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
								]),
						])
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
								])
								->columns(4),
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

		protected static function applyInlineEditingToColumns(array $columns): array
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
								$value = data_get($record, $field);

								if ($value instanceof \DateTimeInterface) {
										$value = $value->format('Y-m-d H:i:s');
								} elseif (is_array($value)) {
										$value = json_encode($value, JSON_UNESCAPED_UNICODE);
								} elseif ($value === null) {
										$value = '';
								}

								return [
										'data-inline-editable' => '1',
										'data-inline-field' => $field,
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

		public static function infolist(Infolist $infolist): Infolist
		{
				return $infolist
						->schema([
								Infolists\Components\Section::make('Основная информация')
										->schema([
												Infolists\Components\TextEntry::make('id')
														->label('№ заявки'),
												
												Infolists\Components\TextEntry::make('created_at')
														->label('Дата и время создания')
														->dateTime('d.m.Y H:i'),
												
												Infolists\Components\TextEntry::make('agent.title')
														->label('Отправитель')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('agent.name')
														->label('Контактное лицо')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('agent.phone')
														->label('Номер телефона')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('agent.email')
														->label('Email')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(2),
								
								Infolists\Components\Section::make('Информация о доставке')
										->schema([
												Infolists\Components\TextEntry::make('delivery_date')
														->label('Дата поставки на РЦ')
														->date('d.m.Y')
														->extraAttributes(fn (Order $record) => $record->hasChanged('delivery_date') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('distributor_id')
														->label('РЦ')
														->extraAttributes(fn (Order $record) => $record->hasChanged('distributor_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('distributor_center_id')
														->label('Адрес РЦ')
														->extraAttributes(fn (Order $record) => $record->hasChanged('distributor_center_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('warehouse_id')
														->label('Склад')
														->extraAttributes(fn (Order $record) => $record->hasChanged('warehouse_id') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(2),
								
								Infolists\Components\Section::make('Груз')
										->schema([
												Infolists\Components\TextEntry::make('cargo')
														->label('Тип груза')
														->formatStateUsing(fn ($state) => match($state) {
																'boxes' => 'Коробки',
																'pallets' => 'Палеты',
																default => $state
														})
														->extraAttributes(fn (Order $record) => $record->hasChanged('cargo') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('pallets_count')
														->label('Кол-во палет')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_count') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('pallets_boxcount')
														->label('Коробов в палете')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_boxcount') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('pallets_weight')
														->label('Вес палет, кг')
														->suffix(' кг')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_weight') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('pallets_volume')
														->label('Объем палет, м³')
														->suffix(' м³')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('pallets_volume') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('boxes_count')
														->label('Кол-во коробов')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('boxes_count') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('boxes_weight')
														->label('Вес коробов, кг')
														->suffix(' кг')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('boxes_weight') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('boxes_volume')
														->label('Объем коробов, м³')
														->suffix(' м³')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('boxes_volume') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('cargo_comment')
														->label('Комментарий')
														->default('—')
														->columnSpanFull()
														->extraAttributes(fn (Order $record) => $record->hasChanged('cargo_comment') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(3),
								
								Infolists\Components\Section::make('Стоимость')
										->schema([
												Infolists\Components\TextEntry::make('payment_method')
														->label('Способ оплаты')
														->formatStateUsing(fn ($state) => match($state) {
																'cash' => 'Наличные',
																'bill' => 'Безналичный',
																default => $state
														})
														->extraAttributes(fn (Order $record) => $record->hasChanged('payment_method') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\IconEntry::make('individual')
														->label('Индивидуальный расчет')
														->boolean()
														->extraAttributes(fn (Order $record) => $record->hasChanged('individual') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('pick')
														->label('Забор груза')
														->money('RUB')
														->extraAttributes(fn (Order $record) => $record->hasChanged('pick') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('delivery')
														->label('Доставка')
														->money('RUB')
														->extraAttributes(fn (Order $record) => $record->hasChanged('delivery') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('additional')
														->label('Палетирование')
														->money('RUB')
														->extraAttributes(fn (Order $record) => $record->hasChanged('additional') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('total')
														->label('Итого')
														->money('RUB')
														->weight('bold')
														->extraAttributes(fn (Order $record) => $record->hasChanged('total') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(3),
								
								Infolists\Components\Section::make('Забор груза')
										->schema([
												Infolists\Components\TextEntry::make('transfer_method')
														->label('Способ передачи')
														->formatStateUsing(fn ($state) => match($state) {
																'pick' => 'Забор',
																'receive' => 'Привоз клиентом',
																default => $state
														})
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('transfer_method_pick_date')
														->label('Дата забора груза')
														->dateTime('d.m.Y H:i')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method_pick_date') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('transfer_method_pick_address')
														->label('Адрес забора груза')
														->default('—')
														->columnSpanFull()
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method_pick_address') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('transfer_method_receive_date')
														->label('Дата привоза клиентом')
														->dateTime('d.m.Y H:i')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('transfer_method_receive_date') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(2),
								
								Infolists\Components\Section::make('Реквизиты')
										->schema([
												Infolists\Components\TextEntry::make('agent.inn')
														->label('ИНН')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('agent.ogrn')
														->label('ОГРН')
														->default('—')
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
												
												Infolists\Components\TextEntry::make('agent.address')
														->label('Адрес')
														->default('—')
														->columnSpanFull()
														->extraAttributes(fn (Order $record) => $record->hasChanged('agent_id') ? ['class' => 'text-orange-500'] : []),
										])
										->columns(2)
										->collapsed(),
						]);
		}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
