<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class UserOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total users', User::count()),
            Card::make('Committee', '1'),
            Card::make('Cashiers', '1'),
        ];
    }
}
