<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Filament\Resources\SessionResource\RelationManagers;
use App\Models\Classroom_Schedules;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Schedule;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use App\Models\Group;
use Filament\Forms\Components\Hidden;
use App\Models\Session;
use Filament\Tables\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Hidden::make('group_id')
                    ->default(function () {
                        // Check if we are editing a record
                        // if ($livewire->record) {
                        //     // Return the group_id of the existing record
                        //     return $livewire->record->group_id;
                        // } else {
                            // Fetch the default Group_number from the session or another source
                            $groupId = session('group_id');
                            if ($groupId) {
                                $group = Group::query()->find($groupId);
                                if ($group) {
                                    $groupNumber = $group->Group_number;
                                    Log::info("Default Group Number: $groupNumber");
                                    return $group->id;
                                } else {
                                    Log::warning("Group with ID $groupId not found.");
                                }
                            }
                            return null;
                        // }
                    }),
                Forms\Components\Select::make('employee_id')
                    ->relationship('teacher', 'Full_name')
                    ->required()
                    ->native(false)
                    ->default(function ($livewire) {
                        if ($livewire->record) {
                            return $livewire->record->employee_id;
                        } else {
                            $group_Id = session('group_id');
                            if ($group_Id) {
                                $group = Group::query()->find($group_Id);
                                if ($group) {
                                    $employee_id = $group->employee_id;
                                    $employee = Employee::query()->find($employee_id);
                                    return $employee->id;
                                }
                            }
                            return null;
                        }
                    }),
                Forms\Components\DatePicker::make('Day')
                    ->required()
                    ->native(false),
                Hidden::make('teacher_Attendance')
                    ->required()
                    ->default(false),
                Forms\Components\TextInput::make('Reason')
                    ->maxLength(255),
                Hidden::make('shifting')
                ->default(false),
                Forms\Components\TextInput::make('Unit')
                ->default(0),
                // Forms\Components\TextInput::make('material_covered'),
                // Forms\Components\TextInput::make('homework')
                //     ->maxLength(255),
            ]);
    }
    public static function getEloquentQuery(): Builder
    {
        $groupId = session('group_id');
        return parent::getEloquentQuery()->where('group_id', $groupId);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.Group_number')
                    ->label('Group')
                    ->badge()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('teacher.Full_name')
                    ->label('Teacher'),
                Tables\Columns\TextColumn::make('Day')
                    ->date()
                    ->badge()
                    ->alignCenter()
                    ->searchable(),
                Tables\Columns\IconColumn::make('teacher_Attendance')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('Reason')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('shifting')
                    ->boolean(),
                Tables\Columns\TextColumn::make('material_covered')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Unit'),
                Tables\Columns\TextColumn::make('homework'),
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
                    Tables\Actions\ViewAction::make()->label('info'),
                    Action::make('Shifting')
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->form(function (Form $form) {
                            return $form->schema([
                                Forms\Components\Select::make('Reason')
                                    ->options([
                                        'Because of the students' => 'Because of the students',
                                        'Because of the teacher' => 'Because of the teacher',
                                        'Official holiday' => 'Official holiday'
                                    ])
                                    ->required()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('Notes')
                                    ->maxLength(255),
                            ]);
                        })
                        ->action(function ($record, array $data) {
                            $specifiedSession = Session::query()
                                ->where('id', '=', $record->id)
                                ->first();
                            $Day = $specifiedSession->Day;
                            $group_id = $specifiedSession->group_id;
                            $latestSession = Session::query()
                                ->where('group_id', '=', $group_id)
                                ->orderBy('Day', 'desc')
                                ->first();

                            $group = Group::query()->where('id', '=', $group_id)->first();
                            $employee = Employee::query()->where('id', '=', $group->employee_id)->first();
                            $course = Course::query()->where('id', '=', $group->course_id)->first();
                            $schedules = $course->course_time;

                            $days = $course->Day;

                            $daysMapping = [
                                'SUNDAY' => Carbon::SUNDAY,
                                'MONDAY' => Carbon::MONDAY,
                                'TUESDAY' => Carbon::TUESDAY,
                                'WEDNESDAY' => Carbon::WEDNESDAY,
                                'THURSDAY' => Carbon::THURSDAY,
                                'FRIDAY' => Carbon::FRIDAY,
                                'SATURDAY' => Carbon::SATURDAY,
                            ];

                            $daysOfWeek = array_map(function ($day) use ($daysMapping) {
                                return $daysMapping[strtoupper($day)];
                            }, $days);

                            $latestSessionDate = Carbon::parse($latestSession->Day);

                            usort($daysOfWeek, function ($a, $b) use ($latestSessionDate) {
                                $diffA = $latestSessionDate->copy()->next($a)->diffInDays($latestSessionDate);
                                $diffB = $latestSessionDate->copy()->next($b)->diffInDays($latestSessionDate);
                                return $diffA - $diffB;
                            });

                            $nextSessionDate = null;
                            foreach ($daysOfWeek as $day) {
                                $nextDate = $latestSessionDate->copy()->next($day);
                                if ($nextDate > $latestSessionDate) {
                                    $nextSessionDate = $nextDate->format('Y-m-d');
                                    break;
                                }
                            }

                            $schedule_employees = Employee_Schedule::query()->where('Month', '=', $nextSessionDate)
                                ->where('employee_id','=',$employee->id)->get();
                            $notAvailable = false;
                            foreach ($schedule_employees as $schedule_employee) {
                                foreach ($schedules as $schedule) {
                                    $time_parts = explode(" ", $schedule);
                                    $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                    if (!$schedule_employee) {
                                        Notification::make()
                                            ->title("This teacher {$employee->Full_name} doesn't have a schedule on this date {$nextSessionDate}")
                                            ->send();
                                        return null;
                                    } elseif ($schedule_employee->schedule_id == $scheduleId) {
                                        if (!$schedule_employee->available) {
                                            $notAvailable = true;
                                        }
                                    }
                                }
                            }

                            if ($notAvailable) {
                                Notification::make()
                                    ->title("This teacher {$employee->Full_name} is not available on this date {$nextSessionDate}")
                                    ->send();
                            } else {
                                foreach ($schedule_employees as $schedule_employee) {
                                    foreach ($schedules as $schedule) {
                                        $time_parts = explode(" ", $schedule);
                                        $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                        $dayName = \Carbon\Carbon::parse($nextSessionDate)->format('l');
                                        $dayName = strtoupper($dayName);
                                        if ($schedule_employee->schedule_id == $scheduleId) {
                                            $schedule_employee->available = false;
                                            $schedule_employee->save();
                                            $classroom = Classroom_Schedules::query()->create([
                                                'classroom_id' => $group->classroom_id,
                                                'schedule_id' => $scheduleId,
                                                'Month' => $nextSessionDate,
                                                'Day' => $dayName,
                                                'available' => false,
                                            ]);
                                        }
                                    }
                                }
                                if (!$record->shifting) {
                                    $record->update([
                                        'Reason' => $data['Reason'],
                                        'Notes' => $data['Notes'],
                                        'shifting' => true,
                                        'teacher_Attendance' => false,
                                    ]);
                                    Session::query()->create([
                                        'group_id' => $group->id,
                                        'employee_id' => $group->employee_id,
                                        'teacher_Attendance' => false,
                                        'Reason' => '',
                                        'Unit' => $record->Unit,
                                        'shifting' => false,
                                        'material_covered' => '',
                                        'Day' => $nextSessionDate,
                                    ]);
                                    foreach ($schedules as $schedule) {
                                        $time_parts = explode(" ", $schedule);
                                        $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                        $Classroom_Schedules = Classroom_Schedules::query()->where('Month', '=', $Day)
                                            ->where('schedule_id','=',$scheduleId)->get();
                                        foreach ($Classroom_Schedules as $classroom_Schedule) {
                                            $classroom_Schedule->delete();
                                        }
                                    }

                                    if ($data['Reason'] == "Because of the students") {
                                        $employee_schedules = Employee_Schedule::query()->where('Month', '=', $Day)
                                            ->where('employee_id', '=', $employee->id)->get();
                                        foreach ($employee_schedules as $employee_schedule) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                                if($employee_schedule->schedule_id == $scheduleId)
                                                {
                                                    $employee_schedule->available = true;
                                                    $employee_schedule->save();
                                                }
                                            }
                                        }
                                    }

                                    $group = Group::query()->where('id', '=', $group_id)->first();
                                    $group->Ending_Date = $nextSessionDate;
                                    $group->save();
                                }
                            }
                            return null;
                        })->visible(function ($record) {
                            return !$record->shifting;
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Shifting')
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->form(function (Form $form) {
                            return $form->schema([
                                Forms\Components\Select::make('Reason')
                                    ->options([
                                        'Because of the students' => 'Because of the students',
                                        'Because of the teacher' => 'Because of the teacher',
                                        'Official holiday' => 'Official holiday'
                                    ])
                                    ->required()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('Notes')
                                    ->maxLength(255),
                            ]);
                        })
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record)use ($data) {
                                $specifiedSession = Session::query()
                                    ->where('id', '=', $record->id)
                                    ->first();
                                $Day = $specifiedSession->Day;
                                $group_id = $specifiedSession->group_id;
                                $latestSession = Session::query()
                                    ->where('group_id', '=', $group_id)
                                    ->orderBy('Day', 'desc')
                                    ->first();

                                $group = Group::query()->where('id', '=', $group_id)->first();
                                $employee = Employee::query()->where('id', '=', $group->employee_id)->first();
                                $course = Course::query()->where('id', '=', $group->course_id)->first();
                                $schedules = $course->course_time;

                                $days = $course->Day;

                                $daysMapping = [
                                    'SUNDAY' => Carbon::SUNDAY,
                                    'MONDAY' => Carbon::MONDAY,
                                    'TUESDAY' => Carbon::TUESDAY,
                                    'WEDNESDAY' => Carbon::WEDNESDAY,
                                    'THURSDAY' => Carbon::THURSDAY,
                                    'FRIDAY' => Carbon::FRIDAY,
                                    'SATURDAY' => Carbon::SATURDAY,
                                ];

                                $daysOfWeek = array_map(function ($day) use ($daysMapping) {
                                    return $daysMapping[strtoupper($day)];
                                }, $days);

                                $latestSessionDate = Carbon::parse($latestSession->Day);

                                usort($daysOfWeek, function ($a, $b) use ($latestSessionDate) {
                                    $diffA = $latestSessionDate->copy()->next($a)->diffInDays($latestSessionDate);
                                    $diffB = $latestSessionDate->copy()->next($b)->diffInDays($latestSessionDate);
                                    return $diffA - $diffB;
                                });

                                $nextSessionDate = null;
                                foreach ($daysOfWeek as $day) {
                                    $nextDate = $latestSessionDate->copy()->next($day);
                                    if ($nextDate > $latestSessionDate) {
                                        $nextSessionDate = $nextDate->format('Y-m-d');
                                        break;
                                    }
                                }

                                $schedule_employees = Employee_Schedule::query()->where('Month', '=', $nextSessionDate)->get();
                                $notAvailable = false;
                                foreach ($schedule_employees as $schedule_employee) {
                                    foreach ($schedules as $schedule) {
                                        $time_parts = explode(" ", $schedule);
                                        $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                        if (!$schedule_employee) {
                                            Notification::make()
                                                ->title("This teacher {$employee->Full_name} doesn't have a schedule on this date {$nextSessionDate}")
                                                ->send();
                                            return null;
                                        } elseif ($schedule_employee->schedule_id == $scheduleId) {
                                            if (!$schedule_employee->available) {
                                                $notAvailable = true;
                                            }
                                        }
                                    }
                                }

                                if ($notAvailable) {
                                    Notification::make()
                                        ->title("This teacher {$employee->Full_name} is not available on this date {$nextSessionDate}")
                                        ->send();
                                } else {
                                    foreach ($schedule_employees as $schedule_employee) {
                                        foreach ($schedules as $schedule) {
                                            $time_parts = explode(" ", $schedule);
                                            $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                            $dayName = \Carbon\Carbon::parse($nextSessionDate)->format('l');
                                            $dayName = strtoupper($dayName);
                                            if ($schedule_employee->schedule_id == $scheduleId) {
                                                $schedule_employee->available = false;
                                                $schedule_employee->save();
                                                $classroom = Classroom_Schedules::query()->create([
                                                    'classroom_id' => $group->classroom_id,
                                                    'schedule_id' => $scheduleId,
                                                    'Month' => $nextSessionDate,
                                                    'Day' => $dayName,
                                                    'available' => false,
                                                ]);
                                            }
                                        }
                                    }
                                    if (!$record->shifting) {
                                        $record->update([
                                            'Reason' => $data['Reason'],
                                            'Notes' => $data['Notes'],
                                            'shifting' => true,
                                            'teacher_Attendance' => false,
                                        ]);
                                        Session::query()->create([
                                            'group_id' => $group->id,
                                            'employee_id' => $group->employee_id,
                                            'teacher_Attendance' => false,
                                            'Reason' => '',
                                            'Unit' => $record->Unit,
                                            'shifting' => false,
                                            'material_covered' => '',
                                            'Day' => $nextSessionDate,
                                        ]);
                                        foreach ($schedules as $schedule) {
                                            $time_parts = explode(" ", $schedule);
                                            $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                            $Classroom_Schedules = Classroom_Schedules::query()->where('Month', '=', $Day)
                                                ->where('schedule_id', '=', $scheduleId)->get();
                                            foreach ($Classroom_Schedules as $classroom_Schedule) {
                                                $classroom_Schedule->delete();
                                            }
                                        }

                                        if ($data['Reason'] == "Because of the students") {
                                            $employee_schedules = Employee_Schedule::query()->where('Month', '=', $Day)
                                                ->where('employee_id', '=', $employee->id)->get();
                                            foreach ($employee_schedules as $employee_schedule) {
                                                foreach ($schedules as $schedule) {
                                                    $time_parts = explode(" ", $schedule);
                                                    $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                                    if ($employee_schedule->schedule_id == $scheduleId) {
                                                        $employee_schedule->available = true;
                                                        $employee_schedule->save();
                                                    }
                                                }
                                            }
                                        }

                                        $group = Group::query()->where('id', '=', $group_id)->first();
                                        $group->Ending_Date = $nextSessionDate;
                                        $group->save();
                                    }
                                }
                                return null;
                            });
                        })
                ]),
            ]);

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttendanceRelationManager::class,
            RelationManagers\AssignmentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
            'view' => Pages\ViewSessions::route('/{record}/view'),
        ];
    }
}
