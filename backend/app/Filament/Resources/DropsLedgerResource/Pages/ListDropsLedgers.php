<?php

namespace App\Filament\Resources\DropsLedgerResource\Pages;

use App\Filament\Resources\DropsLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDropsLedgers extends ListRecords
{
    protected static string $resource = DropsLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
