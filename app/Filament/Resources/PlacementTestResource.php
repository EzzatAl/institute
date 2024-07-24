<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlacementTestResource\Pages;
use App\Enums\OrderStatus;
use App\Filament\Resources\PlacementTestResource\RelationManagers;
use App\Models\Level;
use App\Models\PlacementTest;
use App\Filament\Clusters\Products\Resources\ProductResource;
use App\Filament\Resources\Shop\OrderResource\Widgets\OrderStats;
use App\Forms\Components\AddressForm;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Student;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Squire\Models\Currency;
use function Sodium\add;

class PlacementTestResource extends Resource
{
    protected static ?string $model = PlacementTest::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationGroup = "Exam Management";
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('level_id')
                    ->relationship('level', 'Number_latter')
                    ->native(false)
                    ->preload()
                    ->searchable(),
                    Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'Full_name')
                    ->native(false),
                    Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'serie')
                    ->native(false),
                Forms\Components\TextInput::make('First_name')
                    ->required()
                     ->rules(['regex:/^[\p{L}]+$/u'])
                    ->maxLength(255)
                    ->default(fn () => request()->query('first_name')),
                Forms\Components\TextInput::make('Last_name')
                    ->required()
                     ->rules(['regex:/^[\p{L}]+$/u'])
                    ->maxLength(255)
                    ->default(fn () => request()->query('last_name')),
                Forms\Components\TextInput::make('Email')
                    ->email()
                    ->placeholder('Example@gmail.com')
                    ->required()
                    ->maxLength(255)
                    ->default(fn () => request()->query('email')),
                Forms\Components\ToggleButtons::make('status')
                    ->inline()
                    ->options(OrderStatus::class)
                    ->default('Not yet'),
                Forms\Components\TextInput::make('Phone_number')
                    ->required()
                    ->placeholder("09 or +963")
                    ->tel()
                    ->rules(['regex:/^[+\d]+$/'])
                    ->minLength(10)
                    ->maxLength(13)
                    ->default(fn () => request()->query('phone_number')),
                Forms\Components\TextInput::make('Home_number')
                    ->required()
                    ->tel()
                    ->minLength(7)
                    ->maxLength(7)
                    ->default(fn () => request()->query('home_number')),
                Forms\Components\TextInput::make('Notes')
                    ->maxLength(255)
                    ->nullable(true),
                Forms\Components\DateTimePicker::make('Date_times')
                    ->label('Appointment')
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('level.Number_latter')
                    ->label('Level')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.Full_name')
                    ->label('Moderator')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.serie')
                    ->label('Subject')
                    ->searchable([
                        "CONCAT(Language, ' ', Type)"
                    ]),
                Tables\Columns\TextColumn::make('first_last_name')
                    ->label('Student Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('Phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Home_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Notes')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('Date_times')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at','desc')
            ->filters([
                Tables\Filters\Filter::make('Date_times')
                    ->form([
                        Forms\Components\DatePicker::make('Date_from')
                            ->native(false)
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('Date_until')
                            ->native(false)
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Date_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('Date_times', '>=', $data['Date_from']),
                            )
                            ->when(
                                $data['Date_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('Date_times', '<=', $data['Date_until']),
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
                ActionsActionGroup::make([
                Tables\Actions\Action::make("Student")
                    ->icon('heroicon-o-user-circle')
                    ->action(function (PlacementTest $record) {
                        switch (true) {
                            case $record->status == OrderStatus::Canceled:
                                $notificationTitle = $record->First_name . ' ' . $record->Last_name . " cannot be a student because their status is canceled.";
                                Notification::make()
                                    ->title($notificationTitle)
                                    ->send();
                                break;
                            case $record->status == OrderStatus::Not_yet:
                                $notificationTitle = $record->First_name . ' ' . $record->Last_name . " cannot be a student because their status is not yet.";
                                Notification::make()
                                    ->title($notificationTitle)
                                    ->send();
                                break;
                            case is_null($record->employee_id) && is_null($record->level_id) :
                                $notificationTitle = "Please fill out the data for student " . $record->First_name . ' ' . $record->Last_name ;
                                Notification::make()
                                    ->title($notificationTitle)
                                    ->send();
                                break;
                            case $record->employee_id == null:
                                $notificationTitle ="Please fill out specific Moderator data for student " . $record->First_name . ' ' . $record->Last_name ;
                                Notification::make()
                                    ->title($notificationTitle)
                                    ->send();
                                break;
                            case $record->level_id == null:
                                $notificationTitle = "Please fill out specific level data for student " . $record->First_name . ' ' . $record->Last_name ;
                                Notification::make()
                                    ->title($notificationTitle)
                                    ->send();
                                break;
                            default:
                            if (Student::query()
                            ->where('First_name', '=', $record->First_name)
                            ->where('Last_name', '=', $record->Last_name)
                            ->where('Email', '=', $record->Email)
                            ->exists()) {
                            
                            $student = Student::query()
                                ->where('First_name', '=', $record->First_name)
                                ->where('Last_name', '=', $record->Last_name)
                                ->where('Email', '=', $record->Email)
                                ->first();
                            
                            $subject = Subject::query()
                                ->where('id', '=', $record->subject_id)
                                ->select(DB::raw("CONCAT(Language, ' ', Type) as concatenated"))
                                ->first()
                                ->concatenated;
                        
                            $level = Level::query()
                                ->where('id', '=', $record->level_id)
                                ->pluck('Number_latter')
                                ->first();
                        
                            $subjectExists = false;
                            $subjectIndex = -1;
                        
                            foreach ($student->Subject as $index => $sub) {
                                if ($sub == $subject) {
                                    $subjectExists = true;
                                    $subjectIndex = $index;
                                    break;
                                }
                            }
                        
                            if ($subjectExists) {
                                $studentLevel = $student->Level[$subjectIndex];
                        
                                if ($studentLevel != $level) {
                                    $student->changeLevel($level,$subjectIndex);
                                }
                            } else {
                                $student->addSubject($subject);
                                $student->addLevel($level);
                        
                                $notificationTitle = 'Success';
                                Notification::make()
                                    ->title($notificationTitle)
                                    ->send();
                            }
                        }
                                else {
                                    $student = new Student();
                                    $student->First_name = $record->First_name;
                                    $student->Last_name = $record->Last_name;
                                    $student->Email = $record->Email;
                                    $student->Subject = [Subject::query()->where('id', '=', $record->subject_id)
                                        ->pluck(DB::raw("CONCAT(Language, ' ', Type)"))->first()];
                                    $student->Level = [Level::query()->where('id', '=', $record->level_id)
                                        ->pluck('Number_latter')->first()];
                                    $student->Phone_number = $record->Phone_number;
                                    $student->Home_number = $record->Home_number;

                                    $homeNumberLastThreeChars = substr($record->Home_number, -3);
                                    $phoneNumberLastThreeChars = substr($record->Phone_number, -3);
                                    $password = $homeNumberLastThreeChars . 'EMN' . $phoneNumberLastThreeChars;
                                    $student->Password = Hash::make($password);
                                    $student->save();

                                    $notificationTitle = $record->First_name . ' ' . $record->Last_name . ' has become a student.';
                                    Notification::make()
                                        ->title($notificationTitle)
                                        ->send();
                                }
                                break;
                        }
                    }),
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlacementTests::route('/'),
            'create' => Pages\CreatePlacementTest::route('/create'),
            'edit' => Pages\EditPlacementTest::route('/{record}/edit'),
            'view' => Pages\ViewPlacementTests::route('/{record}/view'),
        ];
    }
}
