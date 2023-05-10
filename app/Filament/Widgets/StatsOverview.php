<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total sales', 'MYR192,000.00'),
            Card::make('Total inventories', '420'),
            Card::make('Total profit', 'MYR192,000.00'),
        ];
    }
}
