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
										->toggleable(isToggledHiddenByDefault: false),
								
								// Контактное лицо из таблицы agents
								Tables\Columns\TextColumn::make('agent.name')
										->label('Контактное лицо')
										->searchable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),
								
								// Номер телефона из таблицы agents
								Tables\Columns\TextColumn::make('agent.phone')
										->label('Номер телефона')
										->searchable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('delivery_date')
										->label('Дата поставки на РЦ')
										->date('d.m.Y')
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('distributor_id')
										->label('РЦ')
										->searchable()
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('distributor_center_id')
										->label('Адрес РЦ')
										->searchable()
										->limit(40)
										->tooltip(fn ($state) => $state)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('payment_method')
										->label('Способ оплаты')
										->formatStateUsing(fn ($state) => match($state) {
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
												default => $state
										})
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\IconColumn::make('individual')
										->label('Индивид.')
										->boolean()
										->sortable()
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
										->toggleable(isToggledHiddenByDefault: false),

								// Кол-во палет
								Tables\Columns\TextColumn::make('pallets_count')
										->label('Кол-во палет')
										->numeric()
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Кол-во коробов в палете
								Tables\Columns\TextColumn::make('pallets_boxcount')
										->label('Коробов в палете')
										->numeric()
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Вес палет
								Tables\Columns\TextColumn::make('pallets_weight')
										->label('Вес палет, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Объем палет
								Tables\Columns\TextColumn::make('pallets_volume')
										->label('Объем палет, м³')
										->numeric()
										->suffix(' м³')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Кол-во коробов
								Tables\Columns\TextColumn::make('boxes_count')
										->label('Кол-во коробов')
										->numeric()
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Объем коробов
								Tables\Columns\TextColumn::make('boxes_volume')
										->label('Объем коробов, м³')
										->numeric()
										->suffix(' м³')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Палетирование (да/нет)
								Tables\Columns\IconColumn::make('has_palletizing')
										->label('Палетирование')
										->boolean()
										->getStateUsing(fn ($record) => $record->palletizing_count > 0)
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),

								// Паллетирование кол-во
								Tables\Columns\TextColumn::make('palletizing_count')
										->label('Палетирование кол-во')
										->numeric()
										->sortable()
										->default(0)
										->toggleable(isToggledHiddenByDefault: false),

								// Забор груза (да/нет)
								Tables\Columns\IconColumn::make('has_pickup')
										->label('Забор груза')
										->boolean()
										->getStateUsing(fn ($record) => $record->transfer_method === 'pick')
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),

								// Дата привоза клиентом
								Tables\Columns\TextColumn::make('transfer_method_receive_date')
										->label('Дата привоза клиентом')
										->date('d.m.Y H:i')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Оплата за забор груза
								Tables\Columns\TextColumn::make('pick')
										->label('Оплата за забор')
										->money('RUB')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Дата забора груза
								Tables\Columns\TextColumn::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->date('d.m.Y H:i')
										->sortable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),

								// Адрес забора груза
								Tables\Columns\TextColumn::make('transfer_method_pick_address')
										->label('Адрес забора')
										->searchable()
										->limit(30)
										->tooltip(fn ($state) => $state)
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('delivery')
										->label('Доставка')
										->money('RUB')
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('additional')
										->label('Палетирование')
										->money('RUB')
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('total')
										->label('Предварительная сумма')
										->money('RUB')
										->sortable()
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('cargo_comment')
										->label('Комментарий')
										->limit(30)
										->tooltip(fn ($state) => $state)
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('agent.email')
										->label('Email')
										->searchable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('agent.inn')
										->label('ИНН')
										->searchable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),
								
								Tables\Columns\TextColumn::make('agent.ogrn')
										->label('ОГРН')
										->searchable()
										->default('—')
										->toggleable(isToggledHiddenByDefault: false),
						])
						->filters([
								Tables\Filters\SelectFilter::make('payment_method')
										->label('Способ оплаты')
										->options([
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
										]),
								
								Tables\Filters\SelectFilter::make('cargo')
										->label('Тип груза')
										->options([
												'boxes' => 'Коробки',
												'pallets' => 'Палеты',
										]),
						])
						->actions([
								Tables\Actions\ViewAction::make()
										->modalHeading('Информация о заявке')
										->modalWidth('7xl'), // Большая ширина модального окна
								Tables\Actions\EditAction::make(),
						])
						->bulkActions([
								Tables\Actions\BulkActionGroup::make([
										Tables\Actions\DeleteBulkAction::make(),
								]),
						])
						->defaultSort('created_at', 'desc')
						->recordAction(Tables\Actions\ViewAction::class);
		}




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('№ заявки')
                            ->disabled(),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'name')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('delivery_date')
                            ->label('Дата поставки')
                            ->required(),
                        
                        Forms\Components\TextInput::make('distributor_id')
                            ->label('Дистрибьютор')
                            ->required(),
                        
                        Forms\Components\TextInput::make('distributor_center_id')
                            ->label('РЦ')
                            ->required(),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличными',
                                'bill' => 'По счету',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Груз')
                    ->schema([
                        Forms\Components\Select::make('cargo')
                            ->label('Тип груза')
                            ->options([
                                'boxes' => 'Коробки',
                                'pallets' => 'Палеты',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('boxes_count')
                            ->label('Количество коробок')
                            ->numeric(),
                        
                        Forms\Components\TextInput::make('pallets_count')
                            ->label('Количество палет')
                            ->numeric(),
                        
                        Forms\Components\Textarea::make('cargo_comment')
                            ->label('Комментарий')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
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
														->default('—'),
												
												Infolists\Components\TextEntry::make('agent.name')
														->label('Контактное лицо')
														->default('—'),
												
												Infolists\Components\TextEntry::make('agent.phone')
														->label('Номер телефона')
														->default('—'),
												
												Infolists\Components\TextEntry::make('agent.email')
														->label('Email')
														->default('—'),
										])
										->columns(2),
								
								Infolists\Components\Section::make('Информация о доставке')
										->schema([
												Infolists\Components\TextEntry::make('delivery_date')
														->label('Дата поставки на РЦ')
														->date('d.m.Y'),
												
												Infolists\Components\TextEntry::make('distributor_id')
														->label('РЦ'),
												
												Infolists\Components\TextEntry::make('distributor_center_id')
														->label('Адрес РЦ'),
												
												Infolists\Components\TextEntry::make('warehouse_id')
														->label('Склад'),
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
														}),
												
												Infolists\Components\TextEntry::make('pallets_count')
														->label('Кол-во палет')
														->default('—'),
												
												Infolists\Components\TextEntry::make('pallets_boxcount')
														->label('Коробов в палете')
														->default('—'),
												
												Infolists\Components\TextEntry::make('pallets_weight')
														->label('Вес палет, кг')
														->suffix(' кг')
														->default('—'),
												
												Infolists\Components\TextEntry::make('pallets_volume')
														->label('Объем палет, м³')
														->suffix(' м³')
														->default('—'),
												
												Infolists\Components\TextEntry::make('boxes_count')
														->label('Кол-во коробов')
														->default('—'),
												
												Infolists\Components\TextEntry::make('boxes_weight')
														->label('Вес коробов, кг')
														->suffix(' кг')
														->default('—'),
												
												Infolists\Components\TextEntry::make('boxes_volume')
														->label('Объем коробов, м³')
														->suffix(' м³')
														->default('—'),
												
												Infolists\Components\TextEntry::make('cargo_comment')
														->label('Комментарий')
														->default('—')
														->columnSpanFull(),
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
														}),
												
												Infolists\Components\IconEntry::make('individual')
														->label('Индивидуальный расчет')
														->boolean(),
												
												Infolists\Components\TextEntry::make('pick')
														->label('Забор груза')
														->money('RUB'),
												
												Infolists\Components\TextEntry::make('delivery')
														->label('Доставка')
														->money('RUB'),
												
												Infolists\Components\TextEntry::make('additional')
														->label('Палетирование')
														->money('RUB'),
												
												Infolists\Components\TextEntry::make('total')
														->label('Итого')
														->money('RUB')
														->weight('bold'),
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
														}),
												
												Infolists\Components\TextEntry::make('transfer_method_pick_date')
														->label('Дата забора груза')
														->dateTime('d.m.Y H:i')
														->default('—'),
												
												Infolists\Components\TextEntry::make('transfer_method_pick_address')
														->label('Адрес забора груза')
														->default('—')
														->columnSpanFull(),
												
												Infolists\Components\TextEntry::make('transfer_method_receive_date')
														->label('Дата привоза клиентом')
														->dateTime('d.m.Y H:i')
														->default('—'),
										])
										->columns(2),
								
								Infolists\Components\Section::make('Реквизиты')
										->schema([
												Infolists\Components\TextEntry::make('agent.inn')
														->label('ИНН')
														->default('—'),
												
												Infolists\Components\TextEntry::make('agent.ogrn')
														->label('ОГРН')
														->default('—'),
												
												Infolists\Components\TextEntry::make('agent.address')
														->label('Адрес')
														->default('—')
														->columnSpanFull(),
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
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
