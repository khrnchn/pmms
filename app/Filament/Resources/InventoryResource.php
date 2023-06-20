<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\BrandResource\RelationManagers\InventoriesRelationManager;
use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Filament\Resources\InventoryResource\Widgets\InventoryOverview;
use App\Models\Inventory;
use Awcodes\Shout\Shout;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Facades\Auth;
use PDO;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([

                        Shout::make('danger')
                            ->content(fn ($record) => $record->qty < $record->security_stock ? 'This inventory is low on stock!' : '')
                            ->type('danger')
                            ->hidden(
                                function ($context, ?Inventory $record) {
                                    if ($context == 'create') {
                                        return true;
                                    } elseif ($record->qty > $record->security_stock) {
                                        return true;
                                    }
                                    return false;
                                }
                            )
                            ->columnSpan('full'),

                        Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->lazy()
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('slug')
                                    ->disabled()
                                    ->required()
                                    ->unique(Inventory::class, 'slug', ignoreRecord: true),

                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpan('full'),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Pricing')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                ->label('Price (MYR)')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->columnSpan(1)
                                    ->required(),

                                Forms\Components\TextInput::make('cost')
                                    ->label('Cost per item (MYR)')
                                    ->helperText('Customers won\'t see this price.')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->columnSpan(1)
                                    ->required(),
                            ])
                            ->columns(2),
                        Forms\Components\Section::make('Inventory')
                            ->schema([

                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->rules(['integer', 'min:0'])
                                    ->required(),

                                Forms\Components\TextInput::make('security_stock')
                                    ->helperText('The safety stock is the limit stock for your inventories which alerts you if the inventories stock will soon be out of stock.')
                                    ->numeric()
                                    ->rules(['integer', 'min:0'])
                                    ->default(10)
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Misc')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->inline(false)
                                    ->default(true)
                                    ->helperText('This inventory will be hidden from all sales channels.'),

                                Forms\Components\Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->required()
                                    ->hiddenOn(InventoriesRelationManager::class),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label(__('Quantity'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('security_stock')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\BooleanColumn::make('is_visible')
                    ->label('Visibility')
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->getStateUsing(function ($record) {
                        if ($record->qty < $record->security_stock) {
                            return 'low on stock';
                        } else {
                            return 'in stock';
                        }
                    })
                    ->enum([
                        'low on stock' => 'Low on stock',
                        'in stock' => 'In stock',
                    ])
                    ->colors([
                        'danger' => 'low on stock',
                        'success' => 'in stock',
                    ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('restock')
                    ->action(function ($record, $data) {
                        $quantity = $data['quantity'];

                        $inventory = Inventory::find($record->id);

                        $newQty = $inventory->qty + $quantity;

                        $inventory->qty = $newQty;

                        $inventory->save();

                        Notification::make('restock')
                            ->success()
                            ->body('Successfully restocked ' . $quantity . ' ' . $record->name . '!')
                            ->send();
                    })
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                    ])
                    ->modalWidth('sm')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Inventory report'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            InventoryOverview::class,
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
