<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.reports';
    protected static ?string $navigationLabel = 'Отчеты';
    protected static ?string $navigationGroup = 'Отчеты';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Отчеты';
}
