<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RosterResource\Pages;
use App\Filament\Resources\RosterResource\RelationManagers;
use App\Models\Roster;

use Buildix\Timex\Traits\TimexTrait;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RosterResource extends Resource
{
    use TimexTrait;

    protected static ?string $recordTitleAttribute = 'user_id';
    protected $chosenStartTime;

    protected static ?string $model = Roster::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function getModel(): string
    {
        return config('timex.models.event');
    }

    public static function getModelLabel(): string
    {
        return trans('timex::timex.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('timex::timex.model.pluralLabel');
    }

    public static function getSlug(): string
    {
        return config('timex.resources.slug');
    }

    protected static function getNavigationGroup(): ?string
    {
        return config('timex.pages.group');
    }

    protected static function getNavigationSort(): ?int
    {
        return config('timex.resources.sort', 1);
    }

    protected static function getNavigationIcon(): string
    {
        return config('timex.resources.icon');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        if (!config('timex.resources.shouldRegisterNavigation')) {
            return false;
        }
        if (!static::canViewAny()) {
            return false;
        }

        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('organizer'),
                Select::make('user_id')
                    ->columnSpanFull()
                    ->searchable()
                    ->required()
                    ->label('Employee')
                    ->options(function () {
                        return self::getUserModel()::all()
                            ->pluck(self::getUserModelColumn('name'), self::getUserModelColumn('id'));
                    }),
                Select::make('category')
                    ->label(trans('timex::timex.event.category'))
                    ->columnSpanFull()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return self::isCategoryModelEnabled() ? self::getCategoryModel()::all()
                            ->pluck(self::getCategoryModelColumn('value'), self::getCategoryModelColumn('key'))
                            : config('timex.categories.labels');
                    }),
                Grid::make(3)->schema([
                    DatePicker::make('start')
                        ->label(trans('timex::timex.event.start'))
                        ->columnSpan(function () {
                            return config('timex.resources.isStartEndHidden', false) ? 'full' : 2;
                        })
                        ->inlineLabel()
                        ->default(today())
                        ->minDate(today())
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($get, $set, $state) {
                            if ($get('end') < $state) {
                                $set('end', $state);
                            }
                        })
                        ->extraAttributes([
                            'class' => '-ml-2'
                        ])
                        ->firstDayOfWeek(config('timex.week.start')),
                    TimePicker::make('startTime')
                        ->hidden(config('timex.resources.isStartEndHidden', false))
                        ->withoutSeconds()
                        ->disableLabel()
                        ->required()
                        ->default(now()->setMinutes(0)->addHour())
                        ->reactive()
                        ->extraAttributes([
                            'class' => '-ml-2'
                        ])
                        ->afterStateUpdated(function ($set, $state) {
                            $set('endTime', Carbon::parse($state)->addMinutes(30));
                        })
                        ->disabled(function ($get) {
                            return $get('isAllDay');
                        }),
                    DatePicker::make('end')
                        ->label(trans('timex::timex.event.end'))
                        ->inlineLabel()
                        ->columnSpan(function () {
                            return config('timex.resources.isStartEndHidden', false) ? 'full' : 2;
                        })
                        ->default(today())
                        ->minDate(today())
                        ->reactive()
                        ->extraAttributes([
                            'class' => '-ml-2'
                        ])
                        ->firstDayOfWeek(config('timex.week.start')),
                    TimePicker::make('endTime')
                        ->hidden(config('timex.resources.isStartEndHidden', false))
                        ->withoutSeconds()
                        ->disableLabel()
                        ->reactive()
                        ->extraAttributes([
                            'class' => '-ml-2'
                        ])
                        ->default(now()->setMinutes(0)->addHour()->addMinutes(30))
                        ->disabled(function ($get) {
                            return $get('isAllDay');
                        }),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee'),
                TextColumn::make('start')
                    ->label(trans('timex::timex.event.start'))
                    ->date()
                    ->description(fn ($record) => $record->startTime),
                TextColumn::make('end')
                    ->label(trans('timex::timex.event.end'))
                    ->date()
                    ->description(fn ($record) => $record->endTime),
                BadgeColumn::make('category')
                    ->label(trans('timex::timex.event.category'))
                    ->enum(config('timex.categories.labels'))
                    ->formatStateUsing(function ($record) {
                        if (\Str::isUuid($record->category)) {
                            return self::getCategoryModel() == null ? "" : self::getCategoryModel()::findOrFail($record->category)->getAttributes()[self::getCategoryModelColumn('value')];
                        } else {
                            return config('timex.categories.labels')[$record->category] ?? "";
                        }
                    })
                    ->color(function ($record) {
                        if (Str::isUuid($record->category)) {
                            return self::getCategoryModel() == null ? "primary" : self::getCategoryModel()::findOrFail($record->category)->getAttributes()[self::getCategoryModelColumn('color')];
                        } else {
                            return config('timex.categories.colors')[$record->category] ?? "primary";
                        }
                    })
            ])->defaultSort('start')
            ->bulkActions([
                DeleteBulkAction::make()->action(function (Collection $records) {
                    return $records->each(function ($record) {
                        return $record->organizer == Auth::id() ? $record->delete() : '';
                    });
                })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRosters::route('/'),
            'create' => Pages\CreateRoster::route('/create'),
            'edit' => Pages\EditRoster::route('/{record}/edit'),
        ];
    }
}
