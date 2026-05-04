<?php

namespace App\Filament\Resources\DropsLedgerResource\Pages;

use App\Filament\Resources\DropsLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDropsLedger extends EditRecord
{
    protected static string $resource = DropsLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
