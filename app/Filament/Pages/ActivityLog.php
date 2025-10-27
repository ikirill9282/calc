<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ActivityLog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string $view = 'filament.pages.activity-log';
    protected static ?string $navigationLabel = 'Логирование';
    protected static ?string $navigationGroup = 'Система';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Логирование действий';
}
