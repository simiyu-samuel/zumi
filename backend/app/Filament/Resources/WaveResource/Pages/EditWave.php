<?php

namespace App\Filament\Resources\WaveResource\Pages;

use App\Filament\Resources\WaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWave extends EditRecord
{
    protected static string $resource = WaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
