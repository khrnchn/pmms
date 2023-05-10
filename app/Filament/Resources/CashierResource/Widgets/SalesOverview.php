<?php

namespace App\Filament\Resources\CashierResource\Widgets;

use Filament\Widgets\BarChartWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SalesOverview extends BarChartWidget
{
    protected static ?string $heading = 'Sales';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Sales (MYR)',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
