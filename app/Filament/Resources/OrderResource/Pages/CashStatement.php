<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Components\Tab;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Tables\Summarizers\ConditionalSum;

class CashStatement extends ListOrders
{
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'cash-statement';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Ведомость по наличным';

    protected static ?string $navigationGroup = 'Отчеты';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Ведомость по наличным';

    protected static string $resource = OrderResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('payment_method', 'cash');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('id')
                ->label('№ заявки')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('agent.title')
                ->label('Отправитель')
                ->searchable()
                ->default('—'),
            Tables\Columns\TextColumn::make('delivery_date')
                ->label('Дата поставки на РЦ')
                ->date('d.m.Y')
                ->placeholder('—'),
            Tables\Columns\TextColumn::make('distribution_label')
                ->label('РЦ и адрес')
                ->default('—')
                ->wrap(),
            Tables\Columns\TextColumn::make('transfer_method_pick_address')
                ->label('Адрес забора')
                ->default('—')
                ->wrap()
                ->visible(fn (): bool => $this->activeTab === 'pickup'),
            Tables\Columns\TextColumn::make('delivery')
                ->label('Доставка')
                ->money('RUB')
                ->summarize(
                    ConditionalSum::make('delivery_sum')
                        ->label('Итого')
                        ->money('RUB')
                        ->recordValueUsing(fn ($record): float => (float) ($record->delivery ?? 0))
                ),
            Tables\Columns\TextColumn::make('additional')
                ->label('Палетирование')
                ->money('RUB')
                ->summarize(
                    ConditionalSum::make('additional_sum')
                        ->label('Итого')
                        ->money('RUB')
                        ->recordValueUsing(fn ($record): float => (float) ($record->additional ?? 0))
                ),
            Tables\Columns\TextColumn::make('pick')
                ->label('Оплата за забор')
                ->money('RUB')
                ->summarize(
                    ConditionalSum::make('pick_sum')
                        ->label('Итого')
                        ->money('RUB')
                        ->recordValueUsing(fn ($record): float => (float) ($record->pick ?? 0))
                ),
            Tables\Columns\TextColumn::make('cash_expected_total')
                ->label('Всего стоимость')
                ->state(fn ($record) => $record->cash_expected_total)
                ->money('RUB')
                ->summarize(
                    ConditionalSum::make('cash_expected_total_sum')
                        ->label('Итого')
                        ->money('RUB')
                        ->recordValueUsing(fn ($record): float => (float) ($record->cash_expected_total ?? 0))
                ),
            Tables\Columns\TextColumn::make('cash_accepted')
                ->label('Принято')
                ->money('RUB')
                ->summarize(
                    ConditionalSum::make('cash_accepted_sum')
                        ->label('Итого')
                        ->money('RUB')
                        ->recordValueUsing(fn ($record): float => (float) ($record->cash_accepted ?? 0))
                ),
            Tables\Columns\TextColumn::make('driver_name')
                ->label('ФИО водителя')
                ->placeholder('—')
                ->color(fn ($record) => $record->hasChanged('driver_name') ? 'warning' : null)
                ->visible(fn (): bool => $this->activeTab === 'pickup'),
        ];

        $columns = OrderResource::applyInlineEditingToColumns($columns);

        return parent::table($table)
            ->columns($columns);
    }

    public function getTabs(): array
    {
        return [
            'warehouse' => Tab::make('Склад — наличными (забор нет)')
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->where(function (Builder $inner): void {
                        $inner
                            ->whereNull('transfer_method')
                            ->orWhere('transfer_method', '!=', 'pick');
                    });
                }),
            'pickup' => Tab::make('Заборы — наличными (забор да)')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('transfer_method', 'pick')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'warehouse';
    }
}
