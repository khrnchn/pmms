<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleInventory;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // $inventories = $data['inventories'];

        // foreach ($inventories as $inventory) {
        //     $item = Inventory::find($inventory['inventory_id']);
        //     $saleInventory = SaleInventory::find($inventory['inventory_id'])->where('sale_id', '=', $inventory['sale_id'])->first();

        //     dd($inventory, $saleInventory);

        //     if ($saleInventory->qty != $inventory['qty']) {
        //         if ($saleInventory->qty < $inventory['qty']) {
        //             $newQty = $inventory['qty'] - $saleInventory->qty;


        //             if ($newQty > $item->qty) {
        //                 Notification::make()
        //                     ->title('Invalid quantity!')
        //                     ->warning()
        //                     ->send();

        //                 throw new \Exception('Invalid quantity');
        //             } else {
        //                 $item->update(['qty' => $item->qty - $newQty]);
        //             }
        //         } else {
        //             $newQty = $saleInventory->qty - $inventory['qty'];

        //             $item->update(['qty' => $item->qty + $newQty]);
        //         }
        //     }
        // }


        return $data;
    }

    protected function getActions(): array
    {
        return [
            // Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
