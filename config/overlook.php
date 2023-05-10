<?php

use App\Filament\Resources\CashierResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\UserResource;

return [
    'includes' => [
        UserResource::class,
        CashierResource::class,
        ReportResource::class,
    ],
    'excludes' => [
        // App\Filament\Resources\Blog\AuthorResource::class,
    ],
];
