<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomSchedulesResource\Pages;
use App\Filament\Resources\ClassroomSchedulesResource\RelationManagers;
use App\Models\Classroom_Schedules;
use App\Models\ClassroomSchedules;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassroomSchedulesResource extends Resource
{
    protected static ?string $model = Classroom_Schedules::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = "Administration";
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('classroom_id')
                    ->relationship('class_room','Class_Number')
                    ->required()
                    ->preload()
                    ->placeholder("Choose the class room")
                    ->native(false),
                Forms\Components\Select::make('schedule_id')
                    ->relationship('schedule','full_schedule')
                    ->preload()
                    ->required()
                    ->preload()
                    ->placeholder("Choose employee's Sessions")
                    ->native(false),
                Forms\Components\Select::make('Month')
                    ->options([
                        1 => 'January',2 => 'February',3 => 'March',
                        4 => 'April',5 => 'May',6 => 'June',7 => 'July',8 => 'August',
                        9 => 'September',10 => 'October',11 => 'November',12 => 'December',])
                    ->required()
                    ->preload()
                    ->placeholder("Choose employee's Month")
                    ->native(false),
                Forms\Components\Select::make('Day')
                    ->options([
                        "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
                        "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
                    ])
                    ->required()
                    ->preload()
                    ->placeholder("Choose employee's Day")
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('class_room.Class_Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule.full_schedule'),
                Tables\Columns\TextColumn::make('Month')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('Date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('Month', '>=', $date),
                            )
                            ->when(
                                $data['Date_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('Month', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['Date_from'] ?? null) {
                            $indicators['Date_from'] =  Carbon::parse($data['Date_from'])->toFormattedDateString();
                        }
                        if ($data['Date_until'] ?? null) {
                            $indicators['Date_until'] =  Carbon::parse($data['Date_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListClassroomSchedules::route('/'),
            'create' => Pages\CreateClassroomSchedules::route('/create'),
            'edit' => Pages\EditClassroomSchedules::route('/{record}/edit'),
            'info' => Pages\ClassroomsSchedulesInfo::route('/{record}/info')
        ];
    }
}
