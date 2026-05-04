<?php

namespace App\Filament\Resources\GatedRoomResource\Pages;

use App\Filament\Resources\GatedRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGatedRoom extends EditRecord
{
    protected static string $resource = GatedRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
