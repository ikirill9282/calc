<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Телефон')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Дата поставки')
                    ->date('d.m.Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('distributor_center_id')
                    ->label('РЦ')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => 'Наличными',
                        'bill' => 'По счету',
                        default => $state
                    }),
                
                Tables\Columns\TextColumn::make('total')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),
            ])
            ->filters([
                //
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
