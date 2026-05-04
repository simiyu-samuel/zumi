<?php

namespace App\Filament\Resources\GatedRoomResource\Pages;

use App\Filament\Resources\GatedRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGatedRooms extends ListRecords
{
    protected static string $resource = GatedRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
