<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\OrderChangeLog;
use Livewire\WithPagination;

class ActivityLog extends Page
{
    use WithPagination;
    
    protected ?string $maxContentWidth = 'full';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string $view = 'filament.pages.activity-log';
    protected static ?string $navigationLabel = 'Логирование';
    protected static ?string $navigationGroup = 'Система';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Логирование действий';

    protected function getViewData(): array
    {
        return [
            'logs' => OrderChangeLog::with(['order', 'user'])
                ->latest()
                ->paginate(25),
        ];
    }
}
