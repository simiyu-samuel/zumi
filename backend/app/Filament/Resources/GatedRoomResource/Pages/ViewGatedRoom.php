<?php

namespace App\Filament\Resources\GatedRoomResource\Pages;

use App\Filament\Resources\GatedRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGatedRoom extends ViewRecord
{
    protected static string $resource = GatedRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
