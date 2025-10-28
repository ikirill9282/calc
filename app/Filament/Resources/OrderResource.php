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
										->searchable(),
								
								Tables\Columns\TextColumn::make('created_at')
										->label('Дата и время')
										->dateTime('d.m.Y H:i')
										->sortable(),
								
								// Отправитель из таблицы agents
								Tables\Columns\TextColumn::make('agent.title')
										->label('Отправитель (ФИО/ИП/ООО)')
										->searchable()
										->sortable()
										->default('—'),
								
								// Контактное лицо из таблицы agents
								Tables\Columns\TextColumn::make('agent.name')
										->label('Контактное лицо')
										->searchable()
										->default('—'),
								
								// Номер телефона из таблицы agents
								Tables\Columns\TextColumn::make('agent.phone')
										->label('Номер телефона')
										->searchable()
										->default('—'),
								
								Tables\Columns\TextColumn::make('delivery_date')
										->label('Дата поставки на РЦ')
										->date('d.m.Y')
										->sortable(),
								
								Tables\Columns\TextColumn::make('distributor_id')
										->label('РЦ')
										->searchable()
										->sortable(),
								
								Tables\Columns\TextColumn::make('distributor_center_id')
										->label('Адрес РЦ')
										->searchable()
										->limit(40)
										->tooltip(fn ($state) => $state),
								
								Tables\Columns\TextColumn::make('payment_method')
										->label('Способ оплаты')
										->formatStateUsing(fn ($state) => match($state) {
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
												default => $state
										})
										->sortable(),
								
								Tables\Columns\IconColumn::make('individual')
										->label('Индивид.')
										->boolean()
										->sortable(),
								
								// Груз
								Tables\Columns\TextColumn::make('cargo')
										->label('Груз')
										->formatStateUsing(fn ($state) => match($state) {
												'boxes' => 'Коробки',
												'pallets' => 'Палеты',
												default => $state
										})
										->sortable(),

								// Кол-во палет
								Tables\Columns\TextColumn::make('pallets_count')
										->label('Кол-во палет')
										->numeric()
										->sortable()
										->default('—'),

								// Кол-во коробов в палете
								Tables\Columns\TextColumn::make('pallets_boxcount')
										->label('Коробов в палете')
										->numeric()
										->sortable()
										->default('—'),

								// Вес палет
								Tables\Columns\TextColumn::make('pallets_weight')
										->label('Вес палет, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—'),

								// Объем палет
								Tables\Columns\TextColumn::make('pallets_volume')
										->label('Объем палет, м³')
										->numeric()
										->suffix(' м³')
										->sortable()
										->default('—'),

								// Кол-во коробов
								Tables\Columns\TextColumn::make('boxes_count')
										->label('Кол-во коробов')
										->numeric()
										->sortable()
										->default('—'),

								// Объем коробов
								Tables\Columns\TextColumn::make('boxes_volume')
										->label('Объем коробов, м³')
										->numeric()
										->suffix(' м³')
										->sortable()
										->default('—'),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—'),

								// Вес коробов
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг')
										->sortable()
										->default('—'),

								// Палетирование (да/нет)
								Tables\Columns\IconColumn::make('has_palletizing')
										->label('Палетирование')
										->boolean()
										->getStateUsing(fn ($record) => $record->palletizing_count > 0)
										->sortable(),

								// Паллетирование кол-во
								Tables\Columns\TextColumn::make('palletizing_count')
										->label('Палетирование кол-во')
										->numeric()
										->sortable()
										->default(0),

								// Забор груза (да/нет)
								Tables\Columns\IconColumn::make('has_pickup')
										->label('Забор груза')
										->boolean()
										->getStateUsing(fn ($record) => $record->transfer_method === 'pick')
										->sortable(),

								// Дата привоза клиентом
								Tables\Columns\TextColumn::make('transfer_method_receive_date')
										->label('Дата привоза клиентом')
										->date('d.m.Y H:i')
										->sortable()
										->default('—'),

								// Оплата за забор груза
								Tables\Columns\TextColumn::make('pick')
										->label('Оплата за забор')
										->money('RUB')
										->sortable()
										->default('—'),

								// Дата забора груза
								Tables\Columns\TextColumn::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->date('d.m.Y H:i')
										->sortable()
										->default('—'),

								// Адрес забора груза
								Tables\Columns\TextColumn::make('transfer_method_pick_address')
										->label('Адрес забора')
										->searchable()
										->limit(30)
										->tooltip(fn ($state) => $state)
										->default('—'),
								
								Tables\Columns\TextColumn::make('delivery')
										->label('Доставка')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('additional')
										->label('Палетирование')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('total')
										->label('Предварительная сумма')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('cargo_comment')
										->label('Комментарий')
										->limit(30)
										->tooltip(fn ($state) => $state),
								
								Tables\Columns\TextColumn::make('agent.email')
										->label('Email')
										->searchable()
										->default('—'),
								
								Tables\Columns\TextColumn::make('agent.inn')
										->label('ИНН')
										->searchable()
										->default('—'),
								
								Tables\Columns\TextColumn::make('agent.ogrn')
										->label('ОГРН')
										->searchable()
										->default('—'),
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
								Tables\Actions\ViewAction::make(),
								Tables\Actions\EditAction::make(),
						])
						->bulkActions([
								Tables\Actions\BulkActionGroup::make([
										Tables\Actions\DeleteBulkAction::make(),
								]),
						])
						->defaultSort('created_at', 'desc');
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
