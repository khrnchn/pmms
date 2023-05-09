<?php

namespace App\Filament\Resources\CashierResource\Pages;

use App\Filament\Resources\CashierResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashier extends EditRecord
{
    protected static string $resource = CashierResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
