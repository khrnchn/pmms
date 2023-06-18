<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total sales', Sale::sum('total_price')),
            Card::make('Total inventories', Inventory::count()),
            Card::make('Total profit', 'MYR192,000.00'),
        ];
    }
}
