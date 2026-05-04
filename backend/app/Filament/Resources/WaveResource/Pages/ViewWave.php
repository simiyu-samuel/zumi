<?php

namespace App\Filament\Resources\WaveResource\Pages;

use App\Filament\Resources\WaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWave extends ViewRecord
{
    protected static string $resource = WaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
