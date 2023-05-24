<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Filament\Resources\SaleResource\Widgets\SaleStats;
use App\Forms\Components\PaymentForm;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Sale;
use Closure;
use Illuminate\Support\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Request;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Buy item(s)')
                            ->schema(static::getFormSchema('inventories')),

                        Forms\Components\Card::make()
                            ->schema(static::getFormSchema()),
                    ])
                    ->columnSpan(['lg' => fn (?Sale $record) => $record === null ? 3 : 2]),

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (Sale $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (Sale $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Sale $record) => $record === null),
            ])->columns(3);
    }

    public static function getFormSchema(?string $section = null): array
    {
        if ($section === 'inventories') {
            return [
                Forms\Components\Repeater::make('inventories')
                    ->label('buying list')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('inventory_id')
                            ->label('Item')
                            ->searchable()
                            ->options(function () {
                                return Inventory::where('qty', '>', 0)->pluck('name', 'id');
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('unit_price', Inventory::find($state)?->price ?? 0);
                            })
                            ->columns(3)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                            ->required()
                            ->reactive()
                            ->disabled(fn (callable $get) => blank($get('inventory_id')))
                            ->afterStateUpdated(function ($get, callable $set) {
                                $unitPrice = $get('unit_price') ?? 0;
                                $qty = $get('qty') ?? 1;
                                $total = $unitPrice * $qty;
                                $set('total', $total);
                            })
                            ->columns(1),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price (MYR)')
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                            ->disabled()
                            ->numeric()
                            ->required()
                            ->columns(1),

                        Forms\Components\TextInput::make('total')
                            ->label('Total (MYR)')
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                            ->disabled()
                            ->required()
                            ->numeric()
                            ->columns(1),
                    ])
                    ->defaultItems(1)
                    ->disableLabel()
                    ->columns(5)
                    ->columnSpan(2)
                    ->required()
                    ->dehydrated(),

                Card::make()
                    ->schema([
                        Placeholder::make("total_price")
                            ->label("Total Price (MYR)")
                            ->content(function ($get) {
                                $total = collect($get('inventories'))
                                    ->pluck('total')
                                    ->sum();

                                Request::session()->put('total_price', $total);

                                return $total;
                            }),
                    ])
                    ->inlineLabel()
            ];
        }

        return [
            Card::make()
                ->schema([
                    Placeholder::make("total_price_next")
                        ->label("Total Price (MYR)")
                        ->content(Request::session()->get('total_price')),
                ])
                ->inlineLabel()
                ->columnSpan('full'),

            Forms\Components\TextInput::make('payable_amount')
                ->label("Paid Amount (MYR)")
                ->numeric()
                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $totalPrice = Request::session()->get('total_price');
                    $paidAmt = $state;
                    $balanceAmt = $paidAmt - $totalPrice;
                    $set('balance_amount', $balanceAmt);
                })
                ->columns(1),

            Forms\Components\TextInput::make('balance_amount')
                ->label("Balance Amount (MYR)")
                ->numeric()
                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                ->disabled()
                ->required()
                ->columns(1),

            Forms\Components\Select::make('method')
                ->options([
                    PaymentMethod::Cash => 'Cash',
                    PaymentMethod::QRCode => 'QR Code',
                    PaymentMethod::BankAccount => 'Bank transfer',
                ])
                ->required()
                ->columns(1),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cashier name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Jan 01, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Payment record has been deleted.')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            SaleStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name'];
    }

    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'inventories']);
    }
}
