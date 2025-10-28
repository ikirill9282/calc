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
            
            Tables\Columns\TextColumn::make('user.name')
                ->label('Отправитель')
                ->searchable()
                ->default('—'),
            
            Tables\Columns\TextColumn::make('user.name')
                ->label('Контактное лицо')
                ->searchable()
                ->default('—'),
            
            Tables\Columns\TextColumn::make('user.phone')
                ->label('Номер телефона')
                ->searchable()
                ->default('—'),
            
            Tables\Columns\TextColumn::make('delivery_date')
                ->label('Дата поставки на РЦ')
                ->date('d.m.Y')
                ->sortable(),
            
            Tables\Columns\TextColumn::make('distributor_center_id')
                ->label('РЦ')
                ->searchable(),
            
            Tables\Columns\TextColumn::make('payment_method')
                ->label('Способ оплаты')
                ->formatStateUsing(fn ($state) => match($state) {
                    'cash' => 'Наличные',
                    'bill' => 'Безналичный',
                    default => $state
                })
                ->default('—'),
            
            Tables\Columns\IconColumn::make('individual')
                ->label('Индивид. расчет')
                ->boolean(),
            
            Tables\Columns\TextColumn::make('cargo')
                ->label('Груз')
                ->formatStateUsing(fn ($state) => match($state) {
                    'boxes' => 'Коробки',
                    'pallets' => 'Палеты',
                    default => $state
                })
                ->default('—'),
            
            Tables\Columns\TextColumn::make('pallets_count')
                ->label('Кол-во палет')
                ->numeric()
                ->default(0),
            
            Tables\Columns\TextColumn::make('boxes_count')
                ->label('Кол-во коробов')
                ->numeric()
                ->default(0),
            
            Tables\Columns\TextColumn::make('boxes_weight')
                ->label('Вес коробов')
                ->numeric()
                ->suffix(' кг')
                ->default(0),
            
            Tables\Columns\TextColumn::make('boxes_volume')
                ->label('Объем коробов')
                ->numeric()
                ->suffix(' м³')
                ->default(0),
            
            Tables\Columns\TextColumn::make('pick')
                ->label('Забор')
                ->money('RUB')
                ->default(0),
            
            Tables\Columns\TextColumn::make('delivery')
                ->label('Доставка')
                ->money('RUB')
                ->default(0),
            
            Tables\Columns\TextColumn::make('additional')
                ->label('Палетирование')
                ->money('RUB')
                ->default(0),
            
            Tables\Columns\TextColumn::make('total')
                ->label('Итого')
                ->money('RUB')
                ->sortable()
                ->default(0),
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
