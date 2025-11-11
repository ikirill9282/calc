<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class CashStatement extends ListOrders
{
    protected static string $resource = OrderResource::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'cash-statement';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Ведомость по наличным';

    protected static ?string $title = 'Ведомость по наличным';

    protected static ?string $navigationGroup = 'Отчеты';

    protected static ?int $navigationSort = 6;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('payment_method', 'cash');
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
