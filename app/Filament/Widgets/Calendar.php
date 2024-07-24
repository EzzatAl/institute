<?php
namespace App\Filament\Widgets;

use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Filament\Resources\CourseResource;

use App\Filament\Resources\PlacementTestResource;
use App\Models\Course;
use App\Models\Employee_Schedule;
use App\Models\PlacementTest;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
class Calendar extends FullCalendarWidget
{
    public Model | string | null $model = Employee_Schedule::class;
    protected int|string|array $columnSpan = 'full';
    protected array $employeeSchedules = [];


    public function fetchEvents(array $fetchInfo): array
    {
        $employee= Employee_Schedule::query()
            ->join('schedules', 'employee_schedules.schedule_id', '=', 'schedules.id')
            ->join('employees', 'employees.id', '=', 'employee_schedules.employee_id')
            ->select('employees.Full_name', 'employee_schedules.id','employee_schedules.Month', 'schedules.Starting_time', 'schedules.Ending_time', 'employee_schedules.available')
            ->where('employee_schedules.available', '=', true)
            ->get()
            ->map(fn(Employee_Schedule $employee_Schedule) => [
                'id'=> $employee_Schedule->id,
                'title' => $employee_Schedule->Full_name,
                'start' => Carbon::parse($employee_Schedule->Month)->format('Y-m-d') . 'T' . Carbon::parse($employee_Schedule->Starting_time)->format('H:i:s'),
                'end' => Carbon::parse($employee_Schedule->Month)->format('Y-m-d') . 'T' . Carbon::parse($employee_Schedule->Ending_time)->format('H:i:s'),
                'color' => 'rgb(0,100,0)',
                //'url' => EmployeeScheduleResource::getUrl(name: 'info', parameters: ['record' => $employee_Schedule]),
            ])->all();
        $placement = PlacementTest::query()->where('status','=',OrderStatus::Not_yet)
            ->get()
            ->map(fn(PlacementTest $placementTest) => [
                'id'=>$placementTest->id,
                'title' => $placementTest->First_name .$placementTest->Last_name ,
                'start' => Carbon::parse($placementTest->Date_times),
                'color' => 'rgb(255,255,0)',
                'url' => PlacementTestResource::getUrl(name: 'view', parameters: ['record' => $placementTest->id]),
            ])->all();
        $course = Course::query()
            ->join('series','series.id','=','courses.serie_id')
            ->selectRaw("series.id,courses.id as ID, CONCAT(subjects.Language, ' ',subjects.Type,' ',levels.Number_latter) AS series")
            ->join('subjects','subjects.id','=','series.subject_id')
            ->join('levels','levels.id','=','series.level_id')
            ->where('status','=',CourseStatus::To_Open)
            ->get()
            ->map(fn(Course $course) => [
                'id'=>$course->id,
                'title' => $course->series,
                'start' => Carbon::parse($course->Starting_Date),
                'color' => 'rgb(0,0,100)',
                 'url' => CourseResource::getUrl(name: 'view', parameters: ['record' => $course->ID]),
            ])->all();
        return array_merge($employee, $placement,$course);
    }
    public function getFormSchema(): array
    {
        return [
            Select::make('employee_id')
                ->relationship('employee','Full_name')
                ->required()
                ->preload()
                ->placeholder("Choose the employee")
                ->native(false),
            Select::make('schedule_id')
                ->relationship('schedule','full_schedule')
                ->preload()
                ->required()
                ->preload()
                ->placeholder("Choose employee's Sessions")
                ->native(false),
            Select::make('Month')
                ->options([
                    1 => 'January',2 => 'February',3 => 'March',
                    4 => 'April',5 => 'May',6 => 'June',7 => 'July',8 => 'August',
                    9 => 'September',10 => 'October',11 => 'November',12 => 'December',])
                ->required()
                ->preload()
                ->placeholder("Choose employee's Month")
                ->native(false),
            Select::make('Day')
                ->options([
                    "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
                    "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
                ])
                ->required()
                ->preload()
                ->placeholder("Choose employee's Day")
                ->native(false),
        ];
    }
}


