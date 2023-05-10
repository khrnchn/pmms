<?php

namespace App\Filament\Resources\RosterResource\Pages;

use App\Filament\Resources\RosterResource;
use Buildix\Timex\Traits\TimexTrait;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

class ListRosters extends ListRecords
{
    use TimexTrait;
    protected static string $resource = RosterResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if (in_array('participants', Schema::getColumnListing(self::getEventTableName()))) {
            return parent::getTableQuery()
                ->where('organizer', '=', Auth::id())
                ->orWhereJsonContains('participants', Auth::id());
        } else {
            return parent::getTableQuery();
        }
    }
}
