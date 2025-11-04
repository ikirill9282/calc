<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Carbon;
use Filament\Tables\Filters\Filter;
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

		public static function table(Table $table): Table
		{
				return $table
						->columns([
								Tables\Columns\TextColumn::make('id')
										->label('№ заявки')
										->sortable()
										->searchable()
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

								// Кол-во коробов в палете
								Tables\Columns\TextColumn::make('pallets_boxcount')
										->label('Коробов в палете')
										->numeric()
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('pallets_boxcount') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Вес палет
								Tables\Columns\TextColumn::make('pallets_weight')
										->label('Вес палет, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('pallets_weight') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Объем палет
								Tables\Columns\TextColumn::make('pallets_volume')
										->label('Объем палет, м³')
										->numeric()
										->suffix(' м³')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('pallets_volume') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Кол-во коробов
								Tables\Columns\TextColumn::make('boxes_count')
										->label('Кол-во коробов')
										->numeric()
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_count') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),

								// Объем коробов
								Tables\Columns\TextColumn::make('boxes_volume')
										->label('Объем коробов, м³')
										->numeric()
										->suffix(' м³')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('boxes_volume') ? 'warning' : null)
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

								// Оплата за забор груза
								Tables\Columns\TextColumn::make('pick')
										->label('Оплата за забор')
										->money('RUB')
										->sortable()
										->default('—')
										->color(fn (Order $record) => $record->hasChanged('pick') ? 'warning' : null)
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
								
								Tables\Columns\TextColumn::make('delivery')
										->label('Доставка')
										->money('RUB')
										->sortable()
										->color(fn (Order $record) => $record->hasChanged('delivery') ? 'warning' : null)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('additional')
										->label('Палетирование')
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
						])
						->recordClasses(fn (Order $record): array => static::resolveHighlightClasses($record->highlight_color))
						->filters([
								Filter::make('id')
										->label('№ заявки')
										->form([
												Forms\Components\TextInput::make('value')
														->label('№ заявки')
														->numeric()
														->placeholder('Введите №'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->where('id', (int) $value),
												);
										}),
								Filter::make('created_at')
										->label('Дата и время')
										->form([
												Forms\Components\DatePicker::make('from')
														->label('С')
														->placeholder('С'),
												Forms\Components\DatePicker::make('until')
														->label('По')
														->placeholder('По'),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['from'] ?? null,
																fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
														)
														->when(
																$data['until'] ?? null,
																fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
														);
										}),
								Filter::make('agent_title')
										->label('Отправитель (ФИО/ИП/ООО)')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Отправитель')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->whereHas(
																'agent',
																fn (Builder $agentQuery) => $agentQuery->where('title', 'like', '%' . $value . '%'),
														),
												);
										}),
								Filter::make('agent_name')
										->label('Контактное лицо')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Контактное лицо')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->whereHas(
																'agent',
																fn (Builder $agentQuery) => $agentQuery->where('name', 'like', '%' . $value . '%'),
														),
												);
										}),
								Filter::make('agent_phone')
										->label('Номер телефона')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Номер телефона')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->whereHas(
																'agent',
																fn (Builder $agentQuery) => $agentQuery->where('phone', 'like', '%' . $value . '%'),
														),
												);
										}),
								Filter::make('delivery_date')
										->label('Дата поставки на РЦ')
										->form([
												Forms\Components\DatePicker::make('from')
														->label('С'),
												Forms\Components\DatePicker::make('until')
														->label('По'),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['from'] ?? null,
																fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '>=', $date),
														)
														->when(
																$data['until'] ?? null,
																fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '<=', $date),
														);
										}),
								Filter::make('distribution')
										->label('РЦ / Адрес')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Значение')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->where(function (Builder $subQuery) use ($value): Builder {
																$like = '%' . $value . '%';

																return $subQuery
																		->where('distributor_id', 'like', $like)
																		->orWhere('distributor_center_id', 'like', $like);
														}),
												);
										}),
								SelectFilter::make('payment_method')
										->label('Способ оплаты')
										->options([
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
										]),
								TernaryFilter::make('individual')
										->label('Индивидуальный расчет')
										->queries(
												true: fn (Builder $query): Builder => $query->where('individual', true),
												false: fn (Builder $query): Builder => $query->where('individual', false),
										),
								SelectFilter::make('cargo')
										->label('Тип груза')
										->options([
												'boxes' => 'Коробки',
												'pallets' => 'Палеты',
										]),
								Filter::make('pallets_count')
										->label('Кол-во палет')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_count', '>=', (int) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_count', '<=', (int) $value),
														);
										}),
								Filter::make('pallets_boxcount')
										->label('Коробов в палете')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_boxcount', '>=', (int) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_boxcount', '<=', (int) $value),
														);
										}),
								Filter::make('pallets_weight')
										->label('Вес палет, кг')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_weight', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_weight', '<=', (float) $value),
														);
										}),
								Filter::make('pallets_volume')
										->label('Объем палет, м³')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_volume', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pallets_volume', '<=', (float) $value),
														);
										}),
								Filter::make('boxes_count')
										->label('Кол-во коробов')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('boxes_count', '>=', (int) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('boxes_count', '<=', (int) $value),
														);
										}),
								Filter::make('boxes_volume')
										->label('Объем коробов, м³')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('boxes_volume', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('boxes_volume', '<=', (float) $value),
														);
										}),
								Filter::make('boxes_weight')
										->label('Вес коробов, кг')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('boxes_weight', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('boxes_weight', '<=', (float) $value),
														);
										}),
								TernaryFilter::make('has_palletizing')
										->label('Палетирование')
										->queries(
												true: fn (Builder $query): Builder => $query->where('palletizing_count', '>', 0),
												false: fn (Builder $query): Builder => $query->where(
														fn (Builder $inner): Builder => $inner
																->whereNull('palletizing_count')
																->orWhere('palletizing_count', '<=', 0),
												),
										),
								Filter::make('palletizing_count')
										->label('Палетирование кол-во')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('palletizing_count', '>=', (int) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('palletizing_count', '<=', (int) $value),
														);
										}),
								TernaryFilter::make('has_pickup')
										->label('Забор груза')
										->queries(
												true: fn (Builder $query): Builder => $query->where('transfer_method', 'pick'),
												false: fn (Builder $query): Builder => $query->where('transfer_method', '!=', 'pick'),
										),
								SelectFilter::make('transfer_method')
										->label('Способ передачи')
										->options([
												'receive' => 'Привоз клиентом',
												'pick' => 'Забор грузополучателем',
										]),
								Filter::make('transfer_method_receive_date')
										->label('Дата привоза клиентом')
										->form([
												Forms\Components\DateTimePicker::make('from')
														->label('С'),
												Forms\Components\DateTimePicker::make('until')
														->label('По'),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['from'] ?? null,
																fn (Builder $query, $date): Builder => $query->where('transfer_method_receive_date', '>=', $date),
														)
														->when(
																$data['until'] ?? null,
																fn (Builder $query, $date): Builder => $query->where('transfer_method_receive_date', '<=', $date),
														);
										}),
								Filter::make('pick')
										->label('Оплата за забор, ₽')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pick', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('pick', '<=', (float) $value),
														);
										}),
								Filter::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->form([
												Forms\Components\DateTimePicker::make('from')
														->label('С'),
												Forms\Components\DateTimePicker::make('until')
														->label('По'),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['from'] ?? null,
																fn (Builder $query, $date): Builder => $query->where('transfer_method_pick_date', '>=', $date),
														)
														->when(
																$data['until'] ?? null,
																fn (Builder $query, $date): Builder => $query->where('transfer_method_pick_date', '<=', $date),
														);
										}),
								Filter::make('transfer_method_pick_address')
										->label('Адрес забора')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Адрес')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->where('transfer_method_pick_address', 'like', '%' . $value . '%'),
												);
										}),
								Filter::make('delivery')
										->label('Доставка, ₽')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('delivery', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('delivery', '<=', (float) $value),
														);
										}),
								Filter::make('additional')
										->label('Палетирование, ₽')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('additional', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('additional', '<=', (float) $value),
														);
										}),
								Filter::make('total')
										->label('Предварительная сумма, ₽')
										->form([
												Forms\Components\TextInput::make('min')
														->label('Мин')
														->numeric(),
												Forms\Components\TextInput::make('max')
														->label('Макс')
														->numeric(),
										])
										->columns(2)
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['min'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('total', '>=', (float) $value),
														)
														->when(
																$data['max'] ?? null,
																fn (Builder $query, $value): Builder => $query->where('total', '<=', (float) $value),
														);
										}),
								Filter::make('cargo_comment')
										->label('Комментарий')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Комментарий')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->where('cargo_comment', 'like', '%' . $value . '%'),
												);
										}),
								Filter::make('agent_email')
										->label('Email')
										->form([
												Forms\Components\TextInput::make('value')
														->label('Email')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->whereHas(
																'agent',
																fn (Builder $agentQuery) => $agentQuery->where('email', 'like', '%' . $value . '%'),
														),
												);
										}),
								Filter::make('agent_inn')
										->label('ИНН')
										->form([
												Forms\Components\TextInput::make('value')
														->label('ИНН')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->whereHas(
																'agent',
																fn (Builder $agentQuery) => $agentQuery->where('inn', 'like', '%' . $value . '%'),
														),
												);
										}),
								Filter::make('agent_ogrn')
										->label('ОГРН')
										->form([
												Forms\Components\TextInput::make('value')
														->label('ОГРН')
														->placeholder('Введите значение'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query->when(
														$data['value'] ?? null,
														fn (Builder $query, $value): Builder => $query->whereHas(
																'agent',
																fn (Builder $agentQuery) => $agentQuery->where('ogrn', 'like', '%' . $value . '%'),
														),
												);
										}),
						])
						->actions([
								Tables\Actions\Action::make('highlight')
										->label('Выделить')
										->icon('heroicon-o-swatch')
										->color('warning')
										->form([
												Forms\Components\Select::make('highlight_color')
														->label('Цвет')
														->options(static::getHighlightColorOptions())
														->required(),
										])
										->fillForm(fn (Order $record): array => [
												'highlight_color' => $record->highlight_color ?? 'none',
										])
										->action(function (Order $record, array $data, Pages\ListOrders $livewire): void {
												$color = $data['highlight_color'] ?? null;

												$record->highlight_color = $color === 'none' ? null : $color;
												$record->save();
												$livewire->flushCachedTableRecords();
										})
										->after(fn (Pages\ListOrders $livewire) => $livewire->dispatch('refresh')),
								Tables\Actions\ViewAction::make()
										->modalHeading('Информация о заявке')
										->modalWidth('7xl') // Большая ширина модального окна
										->infolist(fn (Infolists\Infolist $infolist) => static::infolist($infolist)),
								Tables\Actions\EditAction::make(),
						])
						->bulkActions([
								Tables\Actions\BulkActionGroup::make([
										Tables\Actions\DeleteBulkAction::make(),
								]),
						])
						->defaultSort('created_at', 'desc')
						->recordAction(Tables\Actions\ViewAction::class)
						->recordUrl(null);
		}

		protected static function getHighlightColorOptions(): array
		{
				return [
						'none' => 'Без цвета',
						'yellow' => 'Жёлтый',
						'red' => 'Красный',
						'green' => 'Зелёный',
						'blue' => 'Синий',
				];
		}

		protected static function resolveHighlightClasses(?string $color): array
		{
				return match ($color) {
						'yellow' => ['bg-yellow-50', 'text-yellow-900', 'hover:bg-yellow-100'],
						'red' => ['bg-red-50', 'text-red-900', 'hover:bg-red-100'],
						'green' => ['bg-green-50', 'text-green-900', 'hover:bg-green-100'],
						'blue' => ['bg-blue-50', 'text-blue-900', 'hover:bg-blue-100'],
						default => [],
				};
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
