<?php

namespace App\Filament\Resources\WaveResource\Pages;

use App\Filament\Resources\WaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaves extends ListRecords
{
    protected static string $resource = WaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
