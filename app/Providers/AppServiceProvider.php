<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::registerViteTheme('resources/css/filament.css');

        Filament::registerNavigationGroups([
            'Shop',
            'Timex',
            'Settings',
        ]);
    }
}
