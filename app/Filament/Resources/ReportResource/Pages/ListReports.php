<?php

namespace App\Filament\Resources\ReportResource\Pages;

use AlperenErsoy\FilamentExport\FilamentExport;
use App\Filament\Resources\ReportResource;
use App\Models\DailyStock;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;

class ListReports extends ListRecords
{

    protected static string $resource = ReportResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('report')
                ->modalWidth('sm')
                ->icon('heroicon-s-document-report')
                ->action(function ($livewire, array $data): void {
                    $date = $data['date'];
                    $startDate = Carbon::parse($date)->startOfDay();
                    $endDate = Carbon::parse($date)->endOfDay();

                    $stocks = DailyStock::whereBetween('created_at', [$startDate, $endDate])->get();

                    // generate report here

                    $livewire->redirect(ReportResource::getUrl('index'));
                })
                ->form([
                    DatePicker::make('date')
                        ->required(),
                ])
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return null;
    }
}
