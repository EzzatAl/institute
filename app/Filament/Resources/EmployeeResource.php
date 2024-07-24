<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\Widgets\CalendarWidget;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\Subject;
use Filament\Forms;
use Filament\Pages\Page as PagesPage;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationGroup = "Employee Management";
    public static function sidebar(Model $record): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            ->sidebarNavigation()
            ->setNavigationItems([
                PageNavigationItem::make('View Employee')
                    ->url(function () use ($record) {
                        return static::getUrl('view', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Edit Employee')
                    ->url(function () use ($record) {
                        return static::getUrl('edit', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Calendar Employee')
                    ->url(function () use ($record) {
                        return static::getUrl('calendar', ['record' => $record->id]);
                    }),
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('First_name')
                    ->rules(['regex:/^[a-zA-Z]+$/'])
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('Last_name')
                    ->rules(['regex:/^[a-zA-Z]+$/'])
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('Email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord:true)
                    ->placeholder('Example@gmail.com'),
                Forms\Components\TextInput::make('Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->dehydrateStateUsing(static fn (null|string $state):
                    null|string =>
                    filled($state) ? Hash::make($state) : null
                    )
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (null|string $state): bool =>
                    filled($state),
                    )
                    ->label(static fn (PagesPage $livewire): string =>
                    ($livewire instanceof Pages\EditEmployee) ? "New Password" : "Password"
                    ),
/*                    Forms\Components\TextInput::make('Position')
                    ->required()
                    ->datalist([
                        'Teacher',
                        'Financial',
                    ])
                    ->placeholder("Write employee's position"),*/
                    Forms\Components\FileUpload::make('image_profile')
                        ->directory('image')
                        ->image()
                        ->acceptedFileTypes(['image/*'])
                        ->required(),
                    Forms\Components\TextInput::make('Nationality')
                            ->required(),
                    Forms\Components\Select::make('Language')
                        ->options(function () {
                            return Subject::query()
                                ->pluck('Language', 'Language');
                        })
                        ->native(false)
                        ->placeholder("Select Teacher's Language")
                        ->required(),
                    Forms\Components\TextInput::make('Phone_number')
                    ->required()
                    ->placeholder("Example: 09 or +963")
                    ->tel()
                    ->rules(['regex:/^[+\d]+$/'])
                    ->minLength(10)
                    ->maxLength(13),
                Forms\Components\TextInput::make('Home_number')
                    ->required()
                    ->tel()
                    ->minLength(7)
                    ->maxLength(7),
                    Forms\Components\TextInput::make('Address')
                    ->required()
                    ->placeholder("Damascus, Baramkeh, next to MAPCO")
                    ->rules(['regex:/^[A-Za-z\/\,\s]+$/'])
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        $Language = Employee::pluck('Language')->unique()->toArray();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Full_name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_profile')
                    ->label('Image')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('Email')
                    ->searchable(),
                /*Tables\Columns\TextColumn::make('Position')
                    ->searchable(),*/
                Tables\Columns\TextColumn::make('Language')
                    ->searchable(),

                Tables\Columns\TextColumn::make('Phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Home_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Address')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('Language')
                    ->options(array_combine($Language, $Language))
                    ->label('Language')
                    ->placeholder('Choose the Language'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ]),
            ]);
    }
    public static function getWidgets(): array
    {
        return [

        ];
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
            'index' => EmployeeResource\Pages\ListEmployees::route('/'),
            'edit' => EmployeeResource\Pages\EditEmployee::route('/{record}/edit'),
            'view' => EmployeeResource\Pages\ViewEmployee::route('/{record}/view'),
            'calendar' => EmployeeResource\Pages\Calendar::route('{record}/calendar'),
            'info' => Pages\EmployeeInfo::route('{record}/info'),
        ];
    }
}
