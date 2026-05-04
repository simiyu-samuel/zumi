<?php

namespace App\Filament\Resources\DropsLedgerResource\Pages;

use App\Filament\Resources\DropsLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDropsLedger extends ViewRecord
{
    protected static string $resource = DropsLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
