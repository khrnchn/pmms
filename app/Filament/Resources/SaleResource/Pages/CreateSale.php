<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\SaleResource;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleInventory;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class CreateSale extends CreateRecord
{
    use HasWizard;

    protected static string $resource = SaleResource::class;

    protected function handleRecordCreation(array $data): Sale
    {
        try {
            $totalSum = array_reduce($data['inventories'], function ($carry, $item) {
                return $carry + $item['total'];
            }, 0);

            // Decrease inventory quantity
            foreach ($data['inventories'] as $item) {
                $inventory = Inventory::find($item['inventory_id']);

                if ($item['qty'] > $inventory->qty) {
                    Notification::make()
                        ->title('Invalid quantity!')
                        ->warning()
                        ->send();
                        
                    throw new \Exception('Invalid quantity');
                }

                $inventory->update(['qty' => $inventory->qty - $item['qty']]);
            }

            $userId = Auth::user()->id;

            $data['user_id'] = $userId;
            $data['total_price'] = $totalSum;

            $sale = Sale::create($data);

            $data['sale_id'] = $sale->id;

            Payment::create($data);

            return $sale;
        } catch (\Exception $e) {
            // Handle the exception or log the error
            // For example:
            throw new \Exception('Error occurred during payment creation: ' . $e->getMessage());
        }
    }

    // protected function afterCreate(): void
    // {
    //     $sale = $this->record;

    //     Notification::make()
    //         ->title('New sale')
    //         ->icon('heroicon-o-shopping-bag')
    //         ->body("**{$sale->inventories->count()} item(s) has been sold.**")
    //         ->actions([
    //             Action::make('View')
    //                 ->url(SaleResource::getUrl('edit', ['record' => $sale])),
    //         ])
    //         ->sendToDatabase(auth()->user());
    // }

    protected function getSteps(): array
    {
        return [
            Step::make('step_1')
                ->label('Buy Item(s)')
                ->schema([
                    Card::make(SaleResource::getFormSchema('inventories'))->columns(),
                ]),

            Step::make('step_2')
                ->label('Payment')
                ->schema([
                    Card::make(SaleResource::getFormSchema()),
                ]),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
