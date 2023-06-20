<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): User
    {
        $user = User::create($data);

        if ($data['role'] == 'cashier') {

            $user->assignRole("cashier");
            $permissions = $user->getPermissionsViaRoles();
            $user->givePermissionTo($permissions);

            return $user;

        } elseif ($data['role'] == 'committee') {

            $user->assignRole("committee");
            $permissions = $user->getPermissionsViaRoles();
            $user->givePermissionTo($permissions);

            return $user;

        }

        return $user;
    }
}
