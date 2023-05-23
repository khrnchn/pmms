<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Filament\Resources\SaleResource\Widgets\SaleStats;
use App\Models\Inventory;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
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
                            ->options(Inventory::query()->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('unit_price', Inventory::find($state)?->price ?? 0))
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->disabled()
                            ->numeric()
                            ->required()
                    ])
                    ->defaultItems(1)
                    ->disableLabel()
                    ->columnSpan(4)
                    ->columns(4)
                    ->required(),
            ];
        }

        return [
            Forms\Components\Section::make('Payment')
                ->schema(PaymentResource::getFormSchema()),
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
