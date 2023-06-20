<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\PaymentMethod;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\InventoryResource\Widgets\InventoryOverview;
use App\Models\DailyStock;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleInventory;
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
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getTableQuery(): Builder
    {
        $securityStock = 10;

        return Inventory::query()
            ->orderByRaw("CASE WHEN qty < $securityStock THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('report')
                ->slideOver()
                ->icon('heroicon-o-document-report')
                ->color('success')
                ->action(function ($livewire, $data): void {

                    // trying to access stockArray from table repeater

                    // save in Daily Stocks

                    // notification
                    Notification::make('report')
                        ->success()
                        ->send();

                    // generate report

                    // redirect 
                    $livewire->redirect('report');
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

                                $startToday = Carbon::today()->startOfDay();
                                $endToday = Carbon::today()->endOfDay();

                                $item['opening'] = DailyStock::where([
                                    'inventory_id' => $key,
                                ])->whereBetween('created_at', [$start, $end])
                                    ->value('after');

                                $item['sold'] = SaleInventory::where([
                                    'inventory_id' => $key,
                                ])->whereBetween('created_at', [$startToday, $endToday])
                                    ->sum('qty');

                                $item['closing'] = Inventory::where([
                                    'id' => $key,
                                ])->value('qty');

                                array_push($data, $item);
                            }

                            // set the table repeater with the data
                            $set('stockArray', $data);
                        }),

                    TableRepeater::make('stockArray')
                        ->columnWidths([
                            'name' => '300px',
                        ])
                        ->default(function ($get, callable $set) {
                            $stockArray = $get('stockArray');
                            $defaultItems = [];

                            if (is_array($stockArray)) {
                                foreach ($stockArray as $item) {
                                    $defaultItems[] = [
                                        'inventory_id' => $item['inventory_id'],
                                        'name' => $item['name'],
                                        'before' => $item['opening'],
                                        'sold' => $item['sold'],
                                        'after' => $item['closing'],
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
                                ->afterStateUpdated(fn ($get, $state, callable $set) => $set('after', $get('after') + $state))
                                ->default(0),

                            TextInput::make('damaged')
                                ->label(__('Damaged'))
                                ->numeric()
                                ->reactive()
                                ->afterStateUpdated(fn ($get, $state, callable $set) => $set('after', $get('after') + $state))
                                ->default(0),

                            TextInput::make('after')
                                ->label(__('Closing'))
                                ->numeric()
                                ->disabled()
                                ->default(function ($get, callable $set) {
                                    $before = $get('before') ?? 0;
                                    $sold = $get('sold') ?? 0;
                                    $damaged = $get('damaged') ?? 0;
                                    $restock = $get('restock') ?? 0;
                                    $after = $before - $sold + $restock - $damaged;
                                    $set('after', $after);
                                }),
                        ]),

                    Group::make()->schema([
                        Fieldset::make('Transactions')
                            ->schema([
                                Placeholder::make('cash')
                                    ->content(
                                        Payment::where('method', PaymentMethod::Cash)
                                            ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                                            ->sum(DB::raw('payable_amount - balance_amount')),
                                    )
                                    ->disabled()
                                    ->columnSpan(1),
                                Placeholder::make('qr')
                                    ->label('QR Pay')
                                    ->content(
                                        Payment::where('method', PaymentMethod::QRCode)
                                            ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                                            ->sum(DB::raw('payable_amount - balance_amount')),
                                    )
                                    ->disabled()
                                    ->columnSpan(1),
                                Placeholder::make('banking')
                                    ->content(
                                        Payment::where('method', PaymentMethod::BankAccount)
                                            ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                                            ->sum(DB::raw('payable_amount - balance_amount')),
                                    )
                                    ->disabled()
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->columnSpan(2),

                        Fieldset::make('Summary')
                            ->schema([
                                Placeholder::make('gross_sale')
                                    ->content(
                                        Payment::whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                                            ->sum(DB::raw('payable_amount - balance_amount')),
                                    )
                                    ->disabled()
                                    ->columnSpan(1),
                                Placeholder::make('profit')
                                    ->content('tak buat lagi')
                                    ->disabled()
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
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
