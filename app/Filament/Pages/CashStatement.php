<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class CashStatement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Ведомость по наличным';

    protected static ?string $title = 'Ведомость по наличным';

    protected static ?string $navigationGroup = 'Отчеты';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.cash-statement';

    public Collection $warehouseCash;

    public Collection $pickupCash;

    public function mount(): void
    {
        $this->warehouseCash = Order::query()
            ->where('payment_method', 'cash')
            ->where(fn ($query) => $query->whereNull('transfer_method')->orWhere('transfer_method', '!=', 'pick'))
            ->latest()
            ->take(100)
            ->get();

        $this->pickupCash = Order::query()
            ->where('payment_method', 'cash')
            ->where('transfer_method', 'pick')
            ->latest()
            ->take(100)
            ->get();
    }
}
