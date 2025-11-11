<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CashStatement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Ведомость по наличным';

    protected static ?string $title = 'Ведомость по наличным';

    protected static ?string $navigationGroup = 'Отчеты';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.cash-statement';
}
