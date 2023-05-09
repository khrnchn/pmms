<?php

namespace App\Filament\Resources\CashierResource\Pages;

use App\Filament\Resources\CashierResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashier extends ViewRecord
{
    protected static string $resource = CashierResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
