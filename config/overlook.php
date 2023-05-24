<?php

<<<<<<< HEAD
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CashierResource;
=======
use App\Filament\Resources\SaleResource;
>>>>>>> sye
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\UserResource;

return [
    'includes' => [
        UserResource::class,
        SaleResource::class,
        InventoryResource::class,
        ReportResource::class,
        BrandResource::class,
        UserResource::class,
    ],
    'excludes' => [
        // App\Filament\Resources\Blog\AuthorResource::class,
    ],
];
