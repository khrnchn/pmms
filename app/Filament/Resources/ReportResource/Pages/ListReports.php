<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\CashierResource\Widgets\SalesOverview;
use App\Filament\Resources\ReportResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Closure;

class ListReports extends ListRecords
{

    protected static string $resource = ReportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesOverview::class,
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return null;
    }
}
