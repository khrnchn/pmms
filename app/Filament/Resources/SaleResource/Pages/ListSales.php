<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\SaleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle'),
                
            Actions\Action::make('Payment list')
                ->icon('heroicon-o-clipboard-list')
                ->action(function ($livewire): void {
                    $livewire->redirect(PaymentResource::getUrl('index'));
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return SaleResource::getWidgets();
    }
}
