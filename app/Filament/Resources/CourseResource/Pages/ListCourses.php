<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Models\Exam;
use App\Filament\Resources\CourseResource;
use App\Models\Classroom_Schedules;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\Session;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ListCourses extends ListRecords
{

    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Holiday')
                ->form(
                    [
                        DatePicker::make('Date_from')
                            ->native(false)
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        DatePicker::make('Date_until')
                            ->native(false)
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                        TextInput::make('Reason')
                            ->maxLength(255)
                    ]
                )
                ->action(function (array $data)
                {
                $records = Session::query()->orWhereBetween('Day',[$data['Date_from'],$data['Date_until']])
                    ->where('shifting','=',0)->get();
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

                    $schedule_employees = Employee_Schedule::query()->where('Month', '=', $nextSessionDate)
                        ->where('employee_id', '=', $employee->id)->get();
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
                                'Reason' => "Official holiday",
                                'Notes' => $data['Reason'],
                                'shifting' => true,
                                'teacher_Attendance' => false,
                            ]);
                            Session::query()->create([
                                'group_id' => $group->id,
                                'employee_id' => $group->employee_id,
                                'teacher_Attendance' => false,
                                'Reason' => '',
                                'shifting' => false,
                                'material_covered' => '',
                                'Day' => $nextSessionDate,
                                'Unit'=>$specifiedSession->Unit
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
                            $group = Group::query()->where('id', '=', $group_id)->first();
                            $group->Ending_Date = $nextSessionDate;
                            $group->save();
                        }
                    }
                    return null;

//                };
                });
                }),
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
{
    return [
        null => Tab::make('All'),
        'Open' => Tab::make()
        ->query(function ($query) {
            $query->where('status', 'Open');
        })
        ->icon('heroicon-m-check-badge')
        ->badgeColor('success')
        ->badge(Course::query()->where('status', 'Open')->count()),
        'To Open' => Tab::make()
            ->query(function ($query) {
                $query->where('status', 'To Open');
            })
            ->icon('heroicon-m-exclamation-triangle')
            ->badgeColor('warning')
            ->badge(Course::query()->where('status', 'To Open')->count()),
        'Finished' => Tab::make()
            ->query(function ($query) {
                $query->where('status', 'Finished');
            })
            ->icon('heroicon-m-x-circle')
            ->badgeColor('danger')
            ->badge(Course::query()->where('status', 'Finished')->count()),
    ];
}

}
