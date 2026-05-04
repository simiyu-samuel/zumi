<?php

namespace App\Filament\Resources\CircleResource\Pages;

use App\Filament\Resources\CircleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCircle extends EditRecord
{
    protected static string $resource = CircleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
