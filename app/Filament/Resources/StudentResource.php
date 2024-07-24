<?php

namespace App\Filament\Resources;

use App\Enums\StudentStatus;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Group;
use App\Models\Level;
use App\Models\Student;
use App\Models\Subject;
use Filament\Pages\Page as PagesPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    public Student $record;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('First_name')
                    ->rules(['regex:/^[\p{L}]+$/u'])
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('Last_name')
                    ->rules(['regex:/^[\p{L}]+$/u'])
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('Subject')
                    ->options(function ()
                    {
                        return Subject::query()
                            ->pluck(DB::raw("CONCAT(Language, ' ', Type)"),DB::raw("CONCAT(Language, ' ', Type)"));
                    })
                    ->required()
                    ->searchable()
                    ->multiple()
                    ->preload()
                ->native(false)
                    ->placeholder('Select the Subject of student'),
                Forms\Components\Select::make('Level')
                    ->options(function ()
                    {
                        return Level::query()
                            ->get('Number_latter')
                            ->pluck('Number_latter', 'Number_latter');
                    })
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->required()
                    ->native(false)
                    ->placeholder('Select the Level of Student'),
                Forms\Components\TextInput::make('Email')
                    ->email()
                    ->placeholder('Example@gmail.com')
                    ->required()
                    ->unique(ignoreRecord:true)
                    ->maxLength(255),
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
                    ($livewire instanceof Pages\EditStudent) ? "New Password" : "Password"
                    ),
//                Forms\Components\DatePicker::make('Date_of_birthday')
//                    ->native(false)
//                    ->required(),
                Forms\Components\ToggleButtons::make('status')
                    ->inline()
                    ->options(StudentStatus::class)
                    ->default('Pending'),
                Forms\Components\TextInput::make('Phone_number')
                    ->required()
                    ->placeholder("09 or +963")
                    ->tel()
                    ->rules(['regex:/^[+\d]+$/'])
                    ->minLength(10)
                    ->maxLength(13),
                Forms\Components\TextInput::make('Home_number')
                    ->required()
                    ->tel()
                    ->minLength(7)
                    ->maxLength(7),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('CONCAT_NAME_RANDOM')
                    ->label('Berlitz Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->label('Student Name'),
                Tables\Columns\TextColumn::make('Subject')
                    ->badge()
                    ->label('Student Subject'),
                Tables\Columns\TextColumn::make('Level')
                    ->formatStateUsing(function ($state) {
                        return is_array($state) ? implode(",", $state) : $state;
                    })
                    ->badge()
                    ->label('Student Level'),
                Tables\Columns\TextColumn::make('Email')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('Date_of_birthday')
//                    ->date(),
                Tables\Columns\TextColumn::make('Phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Home_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionsActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('placment_test')
                        ->label('Placement Test')
                        ->icon('heroicon-o-pencil-square')
                        ->url(function ($record) {
                            return PlacementTestResource::getUrl('create', [
                                'first_name' => $record->First_name,
                                'last_name' => $record->Last_name,
                                'email' => $record->Email,
                                'phone_number' => $record->Phone_number,
                                'home_number' => $record->Home_number,
                            ]);
                        })
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            RelationManagers\ActiveRelationManager::class,
            RelationManagers\PendingRelationManager::class,
            RelationManagers\FinishedRelationManager::class,
            RelationManagers\PlacementTestRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudents::route('/{record}/view')
        ];
    }
}
