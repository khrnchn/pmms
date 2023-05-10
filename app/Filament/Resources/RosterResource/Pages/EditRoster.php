<?php

namespace App\Filament\Resources\RosterResource\Pages;

use App\Filament\Resources\RosterResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoster extends EditRecord
{
    protected static string $resource = RosterResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
