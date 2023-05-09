<?php

use App\Filament\Resources\UserResource;

return [
    'includes' => [
        UserResource::class,
    ],
    'excludes' => [
        // App\Filament\Resources\Blog\AuthorResource::class,
    ],
];
