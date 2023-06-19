<?php

use App\Filament\Resources\BrandResource;
<<<<<<< HEAD
=======
use App\Filament\Resources\SaleResource;
>>>>>>> sye
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\SaleResource;
use App\Filament\Resources\UserResource;

return [
    'includes' => [
        UserResource::class,
        SaleResource::class,
        InventoryResource::class,
        ReportResource::class,
        BrandResource::class,
    ],
    'excludes' => [
        // App\Filament\Resources\Blog\AuthorResource::class,
    ],
];
