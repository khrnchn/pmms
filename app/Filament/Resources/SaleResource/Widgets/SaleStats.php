<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SaleStats extends BaseWidget
{
    protected function getCards(): array
    {
        $saleData = Trend::model(Sale::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            Card::make('Total Sales', Sale::count())
                ->chart(
                    $saleData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            // Card::make('Open orders', Order::whereIn('status', ['open', 'processing'])->count()),
            Card::make('Average Sales (MYR)', number_format(Sale::avg('total_price'), 2)),
        ];
    }
}
