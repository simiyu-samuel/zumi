<?php

namespace App\Filament\Resources\CircleResource\Pages;

use App\Filament\Resources\CircleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCircles extends ListRecords
{
    protected static string $resource = CircleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
