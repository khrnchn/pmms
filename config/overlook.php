<?php

use App\Filament\Resources\CashierResource;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\UserResource;

return [
    'includes' => [
        InventoryResource::class,
        CashierResource::class,
        ReportResource::class,
        UserResource::class,
    ],
    'excludes' => [
        // App\Filament\Resources\Blog\AuthorResource::class,
    ],
];
