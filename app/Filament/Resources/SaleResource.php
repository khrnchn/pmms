<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Filament\Resources\SaleResource\Widgets\SaleStats;
use App\Models\Inventory;
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
                        Forms\Components\Card::make()
                            ->schema(static::getFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Buy item(s)')
                            ->schema(static::getFormSchema('inventories')),

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
            ]);
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
                            ->options(Inventory::query()->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('unit_price', Inventory::find($state)?->price ?? 0);
                            })
                            ->columnSpan(4),

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
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price (MYR)')
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                            ->disabled()
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('total')
                            ->label('Total (MYR)')
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                            ->disabled()
                            ->required()
                            ->numeric()
                            ->columnSpan(1),
                    ])
                    ->defaultItems(1)
                    ->disableLabel()
                    ->columnSpan(4)
                    ->columns(4)
                    ->required(),

                Card::make()
                    ->schema([
                        Placeholder::make("total_price")
                            ->label("Total Price (MYR)")
                            ->content(function ($get) {
                                return collect($get('inventories'))
                                    ->pluck('total')
                                    ->sum();
                            }),
                    ])
                    ->inlineLabel()
                    ->columnSpan(4)
            ];
        }

        return [
            Card::make()
                ->schema([
                    Placeholder::make("total_price_next")
                        ->label("Total Price (MYR)")
                        ->content(function ($get) {
                            return collect($get('inventories'))
                                ->pluck('total')
                                ->sum();
                        }),
                ])
                ->inlineLabel()
                ->columnSpan('full'),

            Forms\Components\TextInput::make('payable_amount')
                ->label("Paid Amount (MYR)")
                ->numeric()
                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $totalPrice = collect($get('inventories'))
                        ->pluck('total')
                        ->sum();
                    $paidAmt = $state;
                    $balanceAmt = $paidAmt - $totalPrice;
                    $set('balance_amount', $balanceAmt);
                }),

            Forms\Components\TextInput::make('balance_amount')
                ->label("Balance Amount (MYR)")
                ->numeric()
                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                ->disabled()
                ->required(),

            Forms\Components\Select::make('method')
                ->options([
                    PaymentMethod::Cash => 'Cash',
                    PaymentMethod::QRCode => 'QR Code',
                    PaymentMethod::BankAccount => 'Bank transfer',
                ])
                ->required(),
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

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\PaymentsRelationManager::class,
    //     ];
    // }

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
}
