<?php

namespace App\Filament\Resources\SheetDataResource\Pages;

use App\Filament\Resources\SheetDataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSheetData extends CreateRecord
{
    protected static string $resource = SheetDataResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
