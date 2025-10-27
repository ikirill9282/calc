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
								
								Tables\Columns\TextColumn::make('agent.title')
										->label('Отправитель')
										->searchable(),
								
								Tables\Columns\TextColumn::make('agent.contact_person')
										->label('Контактное лицо')
										->searchable(),
								
								Tables\Columns\TextColumn::make('agent.phone')
										->label('Номер телефона')
										->searchable(),
								
								Tables\Columns\TextColumn::make('delivery_date')
										->label('Дата поставки на РЦ')
										->date('d.m.Y')
										->sortable(),
								
								Tables\Columns\TextColumn::make('distributor_center_id')
										->label('РЦ')
										->searchable(),
								
								Tables\Columns\TextColumn::make('distributor_center_address')
										->label('Адрес РЦ')
										->limit(30),
								
								Tables\Columns\TextColumn::make('payment_method')
										->label('Способ оплаты')
										->formatStateUsing(fn ($state) => match($state) {
												'cash' => 'Наличные',
												'bill' => 'Безналичный',
												'bill_pick' => 'Безнал забор',
												'bill_palletizing' => 'Безнал палетирование',
												default => $state
										}),
								
								Tables\Columns\IconColumn::make('individual')
										->label('Индивидуальный расчет')
										->boolean(),
								
								Tables\Columns\TextColumn::make('cargo')
										->label('Груз')
										->formatStateUsing(fn ($state) => match($state) {
												'boxes' => 'Коробки',
												'pallets' => 'Палеты',
												default => $state
										}),
								
								Tables\Columns\TextColumn::make('pallets_count')
										->label('Кол-во палет')
										->numeric(),
								
								Tables\Columns\TextColumn::make('boxes_in_pallet_count')
										->label('Кол-во коробов в палете')
										->numeric(),
								
								Tables\Columns\TextColumn::make('pallets_weight')
										->label('Вес палет')
										->numeric()
										->suffix(' кг'),
								
								Tables\Columns\TextColumn::make('pallets_volume')
										->label('Объем палет')
										->numeric()
										->suffix(' м³'),
								
								Tables\Columns\TextColumn::make('boxes_count')
										->label('Кол-во коробов')
										->numeric(),
								
								Tables\Columns\TextColumn::make('boxes_weight')
										->label('Вес коробов, кг')
										->numeric()
										->suffix(' кг'),
								
								Tables\Columns\TextColumn::make('boxes_volume')
										->label('Объем коробов, м³')
										->numeric()
										->suffix(' м³'),
								
								Tables\Columns\TextColumn::make('pick')
										->label('Забор груза')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('delivery')
										->label('Доставка')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('additional')
										->label('Палетирование')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('total')
										->label('Стоимость')
										->money('RUB')
										->sortable(),
								
								Tables\Columns\TextColumn::make('transfer_method_pick_date')
										->label('Дата забора груза')
										->date('d.m.Y')
										->sortable(),
								
								Tables\Columns\TextColumn::make('transfer_method_pick_address')
										->label('Адрес забора груза')
										->limit(30),
								
								Tables\Columns\TextColumn::make('cargo_comment')
										->label('Комментарий')
										->limit(30),
								
								Tables\Columns\TextColumn::make('agent.email')
										->label('Email')
										->searchable(),
								
								Tables\Columns\TextColumn::make('agent.inn')
										->label('ИНН')
										->searchable(),
								
								Tables\Columns\TextColumn::make('agent.ogrn')
										->label('ОГРН/ОГРНИП')
										->searchable(),
								
								Tables\Columns\TextColumn::make('user.name')
										->label('ФИО автора заявки')
										->searchable(),
								
								Tables\Columns\TextColumn::make('user.phone')
										->label('Телефон автора')
										->searchable(),
								
								Tables\Columns\TextColumn::make('user.email')
										->label('Email автора')
										->searchable(),
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
								
								Tables\Filters\Filter::make('created_at')
										->form([
												Forms\Components\DatePicker::make('created_from')
														->label('С даты'),
												Forms\Components\DatePicker::make('created_until')
														->label('По дату'),
										])
										->query(function (Builder $query, array $data): Builder {
												return $query
														->when(
																$data['created_from'],
																fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
														)
														->when(
																$data['created_until'],
																fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
														);
										}),
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
						->defaultSort('created_at', 'desc')
						->poll('30s'); // Автообновление каждые 30 секунд
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
