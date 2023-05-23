<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\InventoryResource\Widgets\InventoryOverview;
use App\Models\Inventory;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('closing')
                ->slideOver()
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->action(function ($livewire, ?Inventory $record): void {
                    // save in DailyStock

                    // generate report

                    // redirect 
                    $livewire->redirect(InventoryResource::getURL('index'));
                })
                ->form([
                    TableRepeater::make('dailystocks')
                        ->label(__('Daily stock record'))
                        ->relationship('dailyStocks')
                        ->hideLabels()
                        ->schema([
                            Select::make('inventory_id')
                                ->label(__('Inventory'))
                                ->options(Inventory::pluck('name', 'id')),

                            TextInput::make('before')
                                ->label(__('Opening'))
                                ->numeric()
                                ->default(35)
                                ->disabled(),

                            TextInput::make('sold')
                                ->label(__('Sold'))
                                ->numeric()
                                ->default(8)
                                ->disabled(),

                            TextInput::make('restock')
                                ->label(__('Restock'))
                                ->numeric()
                                ->default(0),

                            TextInput::make('damaged')
                                ->label(__('Damaged'))
                                ->numeric()
                                ->default(0),

                            TextInput::make('after')
                                ->label(__('Closing'))
                                ->numeric()
                                ->disabled(),
                        ]),

                    Group::make()->schema([
                        Fieldset::make('Transactions')
                            ->schema([
                                Placeholder::make('cash')
                                    ->disabled()
                                    ->content('RM 3.00')
                                    ->columnSpan(1),
                                Placeholder::make('qr')
                                    ->disabled()
                                    ->content('RM 6.00')
                                    ->columnSpan(1),
                                Placeholder::make('banking')
                                    ->disabled()
                                    ->content('RM 9.00')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->columnSpan(2),

                        Fieldset::make('Summary')
                            ->schema([
                                Placeholder::make('gross_sale')
                                    ->disabled()
                                    ->content('RM 12.00')
                                    ->columnSpan(1),
                                Placeholder::make('net_sale')
                                    ->disabled()
                                    ->content('RM 12.00')
                                    ->columnSpan(1),
                                Placeholder::make('profit')
                                    ->disabled()
                                    ->content('RM 12.00')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->columnSpan(2),
                    ])->columns(4),

                ]),

            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InventoryOverview::class,
        ];
    }
}
