<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\InventoryResource\Widgets\InventoryOverview;
use App\Models\DailyStock;
use App\Models\Inventory;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
                    TextInput::make('date')
                        ->hidden()
                        ->default(today())
                        ->afterStateHydrated(function ($set) {
                            $inventories = Inventory::all()->pluck('name', 'id');
                            $data = [];
                            foreach ($inventories as $key => $val) {
                                $item = [];

                                $item['inventory_id'] = $key;

                                $item['name'] = $val;

                                $start = Carbon::yesterday()->startOfDay();
                                $end = Carbon::yesterday()->endOfDay();
                                $item['opening'] = DailyStock::where([
                                    'inventory_id' => $key,
                                ])->whereBetween('created_at', [$start, $end])
                                    ->value('after');

                                $item['sold'] = SalesItem::where([
                                    'inventory_id' => $key,
                                ])->whereBetween('created_at', [$start, $end])
                                    ->value('qty');

                                array_push($data, $item);
                            }

                            // set the table repeater with the data
                            $set('stockArray', $data);
                        }),

                    TableRepeater::make('stockArray')
                        ->default(function ($get) {
                            $stockArray = $get('stockArray');
                            $defaultItems = [];

                            if (is_array($stockArray)) {
                                foreach ($stockArray as $item) {
                                    $defaultItems[] = [
                                        'inventory_id' => $item['inventory_id'],
                                        'name' => $item['name'],
                                        'before' => $item['opening'],
                                        'sold' => $item['sold'],
                                    ];
                                }
                            }

                            return $defaultItems;
                        })
                        ->disableItemCreation()
                        ->disableItemDeletion()
                        ->disableItemMovement()
                        ->label(__('Daily stock record'))
                        ->relationship('dailyStocks')
                        ->hideLabels()
                        ->schema([
                            Hidden::make('inventory_id'),

                            TextInput::make('name')
                                ->label(__('Inventory'))
                                ->disabled(),

                            TextInput::make('before')
                                ->label(__('Opening'))
                                ->numeric()
                                ->disabled(),

                            TextInput::make('sold')
                                ->label(__('Sold'))
                                ->numeric()
                                ->disabled(),

                            TextInput::make('restock')
                                ->label(__('Restock'))
                                ->numeric()
                                ->reactive()
                                ->default(0),

                            TextInput::make('damaged')
                                ->label(__('Damaged'))
                                ->numeric()
                                ->reactive()
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
