<?php

namespace App\Filament\Resources\InventoryResource\Widgets;

use App\Models\Inventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class InventoryOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total inventories', Inventory::all()->count()),
            Card::make('Low on stock', Inventory::where('qty', '<', 10)->count() . ' inventories'),
            Card::make('Hidden inventories', Inventory::where('is_visible', false)->count()),
        ];
    }
}
