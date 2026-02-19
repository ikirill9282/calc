<?php

namespace App\Filament\Resources\SheetDataResource\Pages;

use App\Filament\Resources\SheetDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSheetData extends EditRecord
{
    protected static string $resource = SheetDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
