<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Enums\RegisterCourseStatus;
use App\Filament\Resources\CourseResource;
use App\Filament\Resources\GroupStudentResource;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Classroom_Schedules;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Level;
use App\Models\RegisterCourse;
use App\Models\Schedule;
use App\Models\Serie;
use App\Models\Session;
use App\Models\Student;
use App\Models\Subject;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class ListRegisterCourses extends Page implements HasTable
{
    use InteractsWithTable, HasPageSidebar;

    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.resources.register-course-resource.pages.list-register-courses';
    public Course $record;

    public function getTableQuery(): Builder
    {
        return RegisterCourse::query()->where('course_id', '=', $this->record->id);
    }
    public function getTableColumns(): array
    {
        return [
            TextColumn::make('Course.Course')
                ->searchable(),
            TextColumn::make('student.Berlitz_NAME')
                ->searchable(),
            TextColumn::make('status')
                ->badge(),
            TextColumn::make('Note')
                ->searchable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
    public function getTableHeaderActions(): array
    {
        return [
            Action::make('Confirmation')
                ->label('Confirm')
                ->action(function (array $data) {
                    $employeeId = $data['employee_id'];
                    $classroomId = $data['classroom_id'];
                    $course = Course::query()->find($this->record->id);
                    $schedules = $course->course_time;
                    $Days = $course->Day;
                    $startDate = \Illuminate\Support\Carbon::parse($course->Starting_Date);
                    $dates = [];
                    $daysMapping = [
                        'SUNDAY' => \Illuminate\Support\Carbon::SUNDAY,
                        'MONDAY' => Carbon::MONDAY,
                        'TUESDAY' => Carbon::TUESDAY,
                        'WEDNESDAY' => Carbon::WEDNESDAY,
                        'THURSDAY' => Carbon::THURSDAY,
                        'FRIDAY' => Carbon::FRIDAY,
                        'SATURDAY' => Carbon::SATURDAY,
                    ];

                    // Convert daysOfWeek to their corresponding Carbon constants
                    $days = array_map(function ($day) use ($daysMapping) {
                        return $daysMapping[strtoupper($day)];
                    }, $Days);

                    usort($days, function ($a, $b) use ($startDate) {
                        $diffA = $startDate->copy()->next($a)->diffInDays($startDate);
                        $diffB = $startDate->copy()->next($b)->diffInDays($startDate);
                        return $diffA - $diffB;
                    });
                    // Check if there are any students with "Enrolled" status
                    $enrolledStudents = RegisterCourse::query()
                        ->where('course_id', '=', $this->record->id)
                        ->where('status', '=', 'Enrolled')
                        ->get();
                    $schedules = $course->course_time;
                    foreach ($schedules as $schedule) {
                        $time_parts = explode(" ", $schedule);
                        $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                    }
                    $numEnrolledStudents = $enrolledStudents->count();
                    $serieId = Course::query()->where('id', '=', $this->record->id)->value('serie_id');
                    $category = strtolower(Serie::query()->where('id', '=', $serieId)->value('category'));
                    switch ($category) {
                        case 'kids':

                            if($numEnrolledStudents >= 1 && $numEnrolledStudents <= 3)
                            {
                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    // Check if a group already exists for this course
                                    $existingGroup = Group::query()
                                        ->where('course_id', '=', $this->record->id)
                                        ->where('Ending_Date', '>', Carbon::now())
                                        ->exists();

                                    if ($existingGroup) {
                                        Notification::make()
                                            ->title('A group for this course already exists.')
                                            ->send();
                                        return;
                                    }

                                    $numSessions = 11;
                                    $numberunity = 22;
                                    $numberOfSessions = $numSessions;

                                    // Check if the start date itself is one of the session days
                                    $dates = [];
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }

                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }

                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }

                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);
                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);
                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }

                                        if ($lastDate) {
                                            Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 1,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 7;
                                    $numberunity = 22;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'shifting' => false,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 4 && $numEnrolledStudents <= 5) {
                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    // Check if a group already exists for this course
                                    $existingGroup = Group::query()
                                        ->where('course_id', '=', $this->record->id)
                                        ->where('Ending_Date', '>', Carbon::now())
                                        ->exists();

                                    if ($existingGroup) {
                                        Notification::make()
                                            ->title('A group for this course already exists.')
                                            ->send();
                                        return;
                                    }

                                    $numSessions = 12;
                                    $numberunity = 24;
                                    $numberOfSessions = $numSessions;

                                    // Check if the start date itself is one of the session days
                                    $dates = [];
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }

                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }

                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }

                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);
                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);

                                        foreach ($allButLastDate as $date) {

                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }

                                        if ($lastDate) {
                                            Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 1,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 8;
                                    $numberunity = 24;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'shifting' => false,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 6 && $numEnrolledStudents <= 7) {
                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 15;
                                    $numberunity = 30;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 10;
                                    $numberunity = 30;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'shifting' => false,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 8 && $numEnrolledStudents <= 11) {

                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 18;
                                    $numberunity = 36;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 12;
                                    $numberunity = 36;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 12 && $numEnrolledStudents <= 14) {

                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 20;
                                    $numberunity = 39;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];


                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);

                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        if ($lastDate) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 1,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($allButLastDate as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        if ($lastDate) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleIds = Schedule::query()->where('schedule_name', '=', $scheduleName)->pluck('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id' => $classroomId,
                                                    'schedule_id' => $scheduleIds[0],
                                                    'Month' => $lastDate,
                                                    'Day' => strtoupper(Carbon::parse($lastDate)->format('l')),
                                                    'available' => false
                                                ]);

                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleIds[0])
                                                    ->where('Month', $lastDate)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                                break;
                                            }
                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 13;
                                    $numberunity = 39;
                                    $numberOfSessions = $numSessions;
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            break;
                        case 'adults':
                            if ($numEnrolledStudents >= 1 && $numEnrolledStudents <= 2) {

                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 11;
                                    $numberunity = 22;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 7;
                                    $numberunity = 22;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 3 && $numEnrolledStudents <= 5) {

                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 15;
                                    $numberunity = 30;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 10;
                                    $numberunity = 30;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                                'course_id' => $this->record->id,
                                                'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 6 && $numEnrolledStudents <= 7) {
                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 18;
                                    $numberunity = 35;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);
                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        if ($lastDate) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 1,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($allButLastDate as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        if ($lastDate) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleIds = Schedule::query()->where('schedule_name', '=', $scheduleName)->pluck('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id' => $classroomId,
                                                    'schedule_id' => $scheduleIds[0],
                                                    'Month' => $lastDate,
                                                    'Day' => strtoupper(Carbon::parse($lastDate)->format('l')),
                                                    'available' => false
                                                ]);

                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleIds[0])
                                                    ->where('Month', $lastDate)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                                break;
                                            }
                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    }
                                    else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 12;
                                    $numberunity = 35;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);

                                            // $level = Level::query()
                                            //     ->join('series', 'series.level_id', '=', 'levels.id')
                                            //     ->join('courses', 'courses.serie_id', '=', 'series.id')
                                            //     ->where('courses.id', '=', $this->record->id)
                                            //     ->select('levels.id', 'levels.test_type')
                                            //     ->orderBy('levels.id')
                                            //     ->first();

                                            // $studentexam = Exam::query()->create([
                                            //     'group_student_id' => $groupStudent->id,
                                            //     'exam_type' => $level->test_type, // Set the exam_type based on the fetched value
                                            //     'Written_Test' => 0,
                                            //     'Oral_Test' => 0,
                                            //     'Attendance' => 0,
                                            //     'Participation' => 0,
                                            //     'Home_Work' => 0,
                                            //     'Comunication' => 0,
                                            //     'Vocabulary' => 0,
                                            //     'Structure' => 0,
                                            //     'Mark' => 0,
                                            // ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);

                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        if ($lastDate) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($allButLastDate as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $counter = 2;
                                        if ($lastDate) {
                                            foreach ($schedules as $schedule) {
                                                $counter--;
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleIds = Schedule::query()->where('schedule_name', '=', $scheduleName)->pluck('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id' => $classroomId,
                                                    'schedule_id' => $scheduleIds[0],
                                                    'Month' => $lastDate,
                                                    'Day' => strtoupper(Carbon::parse($lastDate)->format('l')),
                                                    'available' => false
                                                ]);

                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleIds[0])
                                                    ->where('Month', $lastDate)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                                if($counter == 0) {
                                                    break;
                                                }
                                            }
                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents >= 8 && $numEnrolledStudents <= 12) {

                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 21;
                                    $numberunity = 42;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 14;
                                    $numberunity = 42;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'shifting' => false,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents == 13) {

                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 22;
                                    $numberunity = 44;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);
                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        // Create the required number of session records
                                        $sessions = [];
                                        foreach ($dates as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'shifting' => false,
                                                'material_covered' => '',
                                            ]);

                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($dates as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 15;
                                    $numberunity = 44;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);

                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        if ($lastDate) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($allButLastDate as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $counter = 2;
                                        if ($lastDate) {
                                            foreach ($schedules as $schedule) {
                                                $counter--;
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleIds = Schedule::query()->where('schedule_name', '=', $scheduleName)->pluck('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id' => $classroomId,
                                                    'schedule_id' => $scheduleIds[0],
                                                    'Month' => $lastDate,
                                                    'Day' => strtoupper(Carbon::parse($lastDate)->format('l')),
                                                    'available' => false
                                                ]);

                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleIds[0])
                                                    ->where('Month', $lastDate)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                                if($counter == 0) {
                                                    break;
                                                }
                                            }
                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            elseif ($numEnrolledStudents == 14) {
                                $courseTimes = Course::query()->where('id', '=', $this->record->id)->value('course_time');
                                if (!empty($courseTimes) && count($courseTimes) == 2) {
                                    $numSessions = 24;
                                    $numberunity = 47;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);

                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        if ($lastDate) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 1,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($allButLastDate as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        if ($lastDate) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleIds = Schedule::query()->where('schedule_name', '=', $scheduleName)->pluck('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id' => $classroomId,
                                                    'schedule_id' => $scheduleIds[0],
                                                    'Month' => $lastDate,
                                                    'Day' => strtoupper(Carbon::parse($lastDate)->format('l')),
                                                    'available' => false
                                                ]);

                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleIds[0])
                                                    ->where('Month', $lastDate)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                                break;
                                            }
                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                                if (!empty($courseTimes) && count($courseTimes) == 3) {
                                    $numSessions = 16;
                                    $numberunity = 47;
                                    $numberOfSessions = $numSessions;
                                    // Check if the start date itself is one of the session days
                                    if (in_array($startDate->dayOfWeek, $days)) {
                                        $dates[] = $startDate->format('Y-m-d');
                                        $numberOfSessions--;
                                    }
                                    while ($numberOfSessions > 0) {
                                        foreach ($days as $day) {
                                            $nextDate = $startDate->copy()->next($day);
                                            $dates[] = $nextDate->format('Y-m-d');
                                            $startDate = $nextDate;
                                            $numberOfSessions--;
                                            if ($numberOfSessions <= 0) {
                                                break;
                                            }
                                        }
                                    }
                                    if ($numSessions > 0) {
                                        $counter = Group::query()->latest('counter')->value('counter');
                                        $currentYear = Carbon::now()->year;
                                        $lastGroup = Group::query()->where('course_id', '=', $this->record->id)->latest('created_at')->first();
                                        if ($lastGroup && $currentYear != $lastGroup->created_at->year) {
                                            $counter = 0;
                                        }
                                        $group = Group::query()->create([
                                            'course_id' => $this->record->id,
                                            'employee_id' => $employeeId,
                                            'classroom_id'=>$classroomId,
                                            'Group_number' => Carbon::now()->format('y-m') . '-' . ($counter + 1),
                                            'Number_Of_Units' => $numberunity,
                                            'Ending_Date' => end($dates),
                                            'counter' => $counter + 1,
                                        ]);

                                        $students = $enrolledStudents->take($numEnrolledStudents);

                                        foreach ($students as $student) {
                                            $groupStudent = GroupStudent::query()->create([
                                                'group_id' => $group->id,
                                                'student_id' => $student->student_id,
                                                'Mark' => 0,
                                            ]);
                                        }
                                        $sessions = [];
                                        $allButLastDate = array_slice($dates, 0, -1);
                                        $lastDate = end($dates);

                                        foreach ($allButLastDate as $date) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $date,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 3,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        if ($lastDate) {
                                            $session = Session::query()->create([
                                                'group_id' => $group->id,
                                                'employee_id' => $employeeId,
                                                'Day' => $lastDate,
                                                'teacher_Attendance' => false,
                                                'Reason' => null,
                                                'shifting' => false,
                                                'Unit' => 2,
                                                'homework'=>null,
                                                'material_covered' => '',
                                            ]);
                                            $sessions[] = $session;
                                        }
                                        foreach ($students as $student) {
                                            $stu = Student::query()->find($student->student_id);
                                            $stu->update([
                                                'status' => 'Active',
                                                'updated_at' => Carbon::now()
                                            ]);
                                            $stu->save();
                                        }
                                        foreach ($allButLastDate as $date) {
                                            foreach ($schedules as $schedule) {
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleId = Schedule::query()->where('schedule_name', '=', $scheduleName)->value('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id'=>$classroomId,
                                                    'schedule_id'=>$scheduleId,
                                                    'Month'=>$date,
                                                    'Day' => strtoupper(Carbon::parse($date)->format('l')),
                                                    'available'=>false
                                                ]);
                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleId)
                                                    ->where('Month', $date)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                            }

                                        }
                                        $counter = 2;
                                        if ($lastDate) {
                                            foreach ($schedules as $schedule) {
                                                $counter--;
                                                $time_parts = explode(" ", $schedule);
                                                $scheduleName = $time_parts[0];
                                                $scheduleIds = Schedule::query()->where('schedule_name', '=', $scheduleName)->pluck('id');
                                                Classroom_Schedules::query()->create([
                                                    'classroom_id' => $classroomId,
                                                    'schedule_id' => $scheduleIds[0],
                                                    'Month' => $lastDate,
                                                    'Day' => strtoupper(Carbon::parse($lastDate)->format('l')),
                                                    'available' => false
                                                ]);

                                                Employee_Schedule::query()
                                                    ->where('employee_id', $employeeId)
                                                    ->where('schedule_id', '=', $scheduleIds[0])
                                                    ->where('Month', $lastDate)
                                                    ->update([
                                                        'available' => false
                                                    ]);
                                                if($counter == 0) {
                                                    break;
                                                }
                                            }
                                        }
                                        $course->status = 'Open';
                                        $course->save();
                                    } else {
                                        Notification::make()
                                            ->title('Insufficient number of enrolled students to create sessions.')
                                            ->send();
                                    }
                                }
                            }
                            break;
                    }
                    foreach($enrolledStudents as $enrolledStudent)
                    {
                        RegisterCourse::query()
                        ->where('course_id','=',$enrolledStudent['course_id'])
                        ->where('student_id','=',$enrolledStudent['student_id'])
                        ->delete();
                    }
                })
                ->form(function (Form $form) {
                    $course = Course::query()->find($this->record->id);
                    $schedules = $course->course_time;
                    $enrolledStudents = RegisterCourse::query()
                        ->where('course_id', '=', $this->record->id)
                        ->where('status', '=', 'Enrolled')
                        ->get();
                    $numEnrolledStudents = $enrolledStudents->count();
                    $serieId = Course::query()->where('id', '=', $this->record->id)->value('serie_id');
                    $category = strtolower(Serie::query()->where('id', '=', $serieId)->value('category'));
                    $subjectid = Serie::query()->where('id', '=', $serieId)->value('subject_id');
                    $language = Subject::query()->where('id', '=', $subjectid)->value('Language');
                    $numSessions = 0;
                    switch ($category) {
                        case 'kids':
                            if ($numEnrolledStudents >= 4 && $numEnrolledStudents <= 5) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 12;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 8;
                                }
                            } elseif ($numEnrolledStudents >= 6 && $numEnrolledStudents <= 7) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 15;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 10;
                                }
                            } elseif ($numEnrolledStudents >= 8 && $numEnrolledStudents <= 11) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 18;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 12;
                                }
                            } elseif ($numEnrolledStudents >= 12 && $numEnrolledStudents <= 14) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 20;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 13;
                                }
                            }
                            break;
                        case 'adults':
                            if ($numEnrolledStudents >= 3 && $numEnrolledStudents <= 5) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 15;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 10;
                                }
                            } elseif ($numEnrolledStudents >= 6 && $numEnrolledStudents <= 7) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 18;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 12;
                                }
                            } elseif ($numEnrolledStudents >= 8 && $numEnrolledStudents <= 12) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 21;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 14;
                                }
                            } elseif ($numEnrolledStudents == 13) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 22;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 15;
                                }
                            } elseif ($numEnrolledStudents == 14) {
                                $courseTimes = $course->course_time;
                                if (count($courseTimes) == 2) {
                                    $numSessions = 24;
                                } elseif (count($courseTimes) == 3) {
                                    $numSessions = 16;
                                }
                            }
                            break;
                    }
                    $employeeHaveWorkHoursIds = [];
                    $employeeIds = [];
                    $classroomIds = [];
                    $Days = $course->Day;
                    $startDate = \Illuminate\Support\Carbon::parse($course->Starting_Date);
                    $dates = [];
                    $daysMapping = [
                        'SUNDAY' => \Illuminate\Support\Carbon::SUNDAY,
                        'MONDAY' => \Illuminate\Support\Carbon::MONDAY,
                        'TUESDAY' => \Illuminate\Support\Carbon::TUESDAY,
                        'WEDNESDAY' => \Illuminate\Support\Carbon::WEDNESDAY,
                        'THURSDAY' => \Illuminate\Support\Carbon::THURSDAY,
                        'FRIDAY' => \Illuminate\Support\Carbon::FRIDAY,
                        'SATURDAY' => \Illuminate\Support\Carbon::SATURDAY,
                    ];

                    $days = array_map(function ($day) use ($daysMapping) {
                        return $daysMapping[strtoupper($day)];
                    }, $Days);

                    usort($days, function ($a, $b) use ($startDate) {
                        $diffA = $startDate->copy()->next($a)->diffInDays($startDate);
                        $diffB = $startDate->copy()->next($b)->diffInDays($startDate);
                        return $diffA - $diffB;
                    });

                    $numberOfSessions = $numSessions;
                    if (in_array($startDate->dayOfWeek, $days)) {
                        $dates[] = $startDate->format('Y-m-d');
                        $numberOfSessions--;
                    }
                    while ($numberOfSessions > 0) {
                        foreach ($days as $day) {
                            $nextDate = $startDate->copy()->next($day);
                            $dates[] = $nextDate->format('Y-m-d');
                            $startDate = $nextDate;
                            $numberOfSessions--;
                            if ($numberOfSessions <= 0) {
                                break;
                            }
                        }
                    }
                  //  dump($dates);
                    foreach ($dates as $date) {
                        foreach ($schedules as $schedule) {
                            $time_parts = explode(" ", $schedule);
                            $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                             if (Employee_Schedule::query()
                                ->where('schedule_id', '=', $scheduleId)
                                ->where('Month', '=', $date)
                                ->where('available','=',false)->exists())
                             {
                                 $employees = Employee_Schedule::query()
                                     ->where('schedule_id', '=', $scheduleId)
                                     ->where('Month', '=', $date)
                                     ->where('available', '=', false)
                                     ->pluck('employee_id')
                                     ->toArray();

                                 if ($employees) {
                                     foreach ($employees as $employeeId) {
                                         $employeeHaveWorkHoursIds[$employeeId] = true;
                                     }
                                 }
                             }
                        }
                    }
                    $employeeHaveWorkHoursIds = array_keys($employeeHaveWorkHoursIds);
                    //dump($employeeHaveWorkHoursIds);
                    $employees = Employee::query()
                        ->whereNotIn('id', $employeeHaveWorkHoursIds)
                        ->where('Language','=',$language)
                        ->get();
                    foreach ($employees as $employee) {
                        foreach ($dates as $date) {
                            foreach ($schedules as $schedule) {
                                $time_parts = explode(" ", $schedule);
                                $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                                if (Employee_Schedule::query()
                                    ->where('schedule_id', '=', $scheduleId)
                                    ->where('Month', '=', $date)
                                    ->where('employee_id', '=', $employee->id))
                                {
                                    $emp = Employee_Schedule::query()
                                        ->where('schedule_id', '=', $scheduleId)
                                        ->where('Month', '=', $date)
                                        ->where('employee_id','=',$employee->id)
                                        ->pluck('employee_id')
                                        ->toArray();
                                    // dump($emp);
                                    if ($emp) {
                                        foreach ($emp as $e) {
                                            $employeeIds[$e] = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $employeeIds = array_keys($employeeIds);
                    // dump($employeeIds);
                    //$empl = array_merge( $employeeIds,$employeeHaveWorkHoursIds);
                    $available_employees = Employee::query()
                        ->whereIn('id', $employeeIds)
                        ->whereNotIn('id', $employeeHaveWorkHoursIds)
                        ->where('Language','=',$language)
                        ->get();
                    foreach ($dates as $date) {
                        foreach ($schedules as $schedule) {
                            $time_parts = explode(" ", $schedule);
                            $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                            if (Classroom_Schedules::query()
                                ->where('schedule_id', '=', $scheduleId)
                                ->where('Month', '=', $date)
                                ->exists()) {
                                $classes = Classroom_Schedules::query()
                                    ->where('schedule_id', '=', $scheduleId)
                                    ->where('Month', '=', $date)
                                    ->pluck('classroom_id')
                                    ->toArray();
                                if ($classes) {
                                    foreach ($classes as $class) {
                                        $classroomIds[$class] = true;
                                    }
                                }
                            }
                        }
                    }

                    $classroom = Classroom::query()
                        ->whereNotIn('id', $classroomIds)
                        ->get();
                    $employeeOptions = $available_employees->pluck('Full_name', 'id')->toArray();
                    $classroomOptions = $classroom->pluck('Class_Number', 'id')->toArray();
                    return [
                        Select::make('employee_id')
                            ->options($employeeOptions)
                            ->preload()
                            ->label('Teacher')
                            ->native(false)
                            ->required(),
                        Select::make('classroom_id')
                            ->options($classroomOptions)
                            ->preload()
                            ->label('Class room')
                            ->native(false)
                            ->required(),
                    ];
                })
            ->visible(function ()
            {
                $register =RegisterCourse::query()->where('course_id','=',$this->record->id)->first();
                $group = Group::query()->where('course_id','=',$this->record->id)->first();
                return !$group && $register;
            }),
            CreateAction::make()
                ->label('Register Student')
                ->form(function () {
                    $defaultCourseId = Course::query()
                        ->join('series', 'series.id', '=', 'courses.serie_id')
                        ->join('subjects', 'subjects.id', '=', 'series.subject_id')
                        ->join('levels', 'levels.id', '=', 'series.level_id')
                        ->selectRaw("courses.id")
                        ->where('courses.id', '=', $this->record->id)
                        ->orderBy('courses.id')
                        ->value('courses.id');
                    $specific = Course::query()
                        ->join('series', 'series.id', '=', 'courses.serie_id')
                        ->join('subjects', 'subjects.id', '=', 'series.subject_id')
                        ->join('levels', 'levels.id', '=', 'series.level_id')
                        ->where('courses.id', '=', $this->record->id)
                        ->selectRaw("series.id, CONCAT(subjects.Language, ' ', subjects.Type) AS subject, levels.Number_latter AS level")
                        ->orderBy('series.id')
                        ->first();
            
                    $students = Student::query()->get();
                    $specificstudent = [];
                    $subjectExists = false;
                    $subjectIndex = -1;
                    foreach ($students as $student ) {
                        foreach ($student->Subject as $index => $sub) {
                            if ($sub == $specific->subject) {
                                $subjectExists = true;
                                $subjectIndex = $index;
                                if ($subjectExists) {
                                    $studentLevel = $student->Level[$subjectIndex];
                                    if ($studentLevel == $specific->level) {
                                        $specificstudent[$student->id] = $student->CONCAT_NAME_RANDOM;
                                    }
                                break;
                            }
                        }
                        }
                    }
                    return [
                        Hidden::make('course_id')
                            ->default($defaultCourseId),
                        Select::make('student_id')
                            ->options($specificstudent)
                            ->preload()
                            ->native(false)
                            ->required(),
                        ToggleButtons::make('status')
                            ->options(RegisterCourseStatus::class)
                            ->inline()
                            ->default('Not yet')
                            ->required(),
                        TextInput::make('Note')
                            ->maxLength(255),
                    ];
                })
                ->before(function ($data, $action) {
                   $existingRegistration = RegisterCourse::query()->where('course_id', '=', $this->record->id)
                       ->where('student_id', '=', $data['student_id'])
                       ->first();

                   if ($existingRegistration) {
                       Notification::make()
                           ->title('The student has already registration in this course ')
                           ->send();
                       $action->halt();
                   }
               })
                //->redirect('group-student.index'),
        ];
    }
    public function getTableActions(): array
    {
        return [
            ViewAction::make()->form(function () {
                return [
                    Select::make('course_id')
                        ->relationship('course', 'Course'),
                    Select::make('student_id')
                        ->relationship('student', 'Berlitz_NAME'),
                    TextInput::make('status'),
                    TextInput::make('Note'),
                ];
            }),
            EditAction::make()->form(function () {
                return [
                    Select::make('course_id')
                        ->relationship('course', 'Course')
                        ->native(false),
                    Select::make('student_id')
                        ->relationship('student', 'Berlitz_NAME')
                        ->native(false),
                    ToggleButtons::make('status')
                        ->options(RegisterCourseStatus::class)
                        ->inline()
                        ->default('Not yet'),
                    TextInput::make('Note'),
                ];
            }),
            DeleteAction::make(),
        ];
    }
    public function getTableBulkActions(): array
    {
        return [];
    }
}
