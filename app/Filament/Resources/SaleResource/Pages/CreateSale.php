<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\SaleResource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateSale extends CreateRecord
{
    use HasWizard;

    protected static string $resource = SaleResource::class;

    protected function afterCreate(): void
    {
        $sale = $this->record;

        Notification::make()
            ->title('New sale')
            ->icon('heroicon-o-shopping-bag')
            ->body("**{$sale->inventories->count()} item(s) has been sold.**")
            ->actions([
                Action::make('View')
                    ->url(SaleResource::getUrl('edit', ['record' => $sale])),
            ])
            ->sendToDatabase(auth()->user());
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Buy Item(s)')
                ->schema([
                    Card::make(SaleResource::getFormSchema('inventories'))->columns(),
                ]),

            Step::make('Payment')
                ->schema([
                    Card::make(PaymentResource::getFormSchema()),
                ]),
        ];
    }
}
