<?php

use App\Filament\Resources\SaleResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\UserResource;

return [
    'includes' => [
        UserResource::class,
        SaleResource::class,
        ReportResource::class,
    ],
    'excludes' => [
        // App\Filament\Resources\Blog\AuthorResource::class,
    ],
];
