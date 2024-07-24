<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeScheduleResource;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | int | string | null $record;
    public function fetchEvents(array $fetchInfo): array
    {
        $employee_not_available =  Employee_Schedule::query()
            ->join('schedules', 'employee_schedules.schedule_id', '=', 'schedules.id')
            ->join('employees', 'employees.id', '=', 'employee_schedules.employee_id')
            ->select('employees.Full_name', 'employee_schedules.id','employee_schedules.Month', 'schedules.Starting_time', 'schedules.Ending_time', 'employee_schedules.available')
            ->where('employee_id', '=', $this->record->id)
            ->where('employee_schedules.available', '=', false)
            ->get()
            ->map(function (Employee_Schedule $task) {
                $isAvailable = $task->available;
                return [
                    'title' => $task->Full_name,
                    'start' => Carbon::parse($task->Month)->format('Y-m-d') . 'T' . Carbon::parse($task->Starting_time)->format('H:i:s'),
                    'end' => Carbon::parse($task->Month)->format('Y-m-d') . 'T' . Carbon::parse($task->Ending_time)->format('H:i:s'),
                    'color' => 'rgb(100,0,0)',
                    'url' => EmployeeResource::getUrl(name: 'info', parameters: ['record' => $task->id]),
                ];
            })
            ->toArray();
        $employee_available  = Employee_Schedule::query()
            ->join('schedules', 'employee_schedules.schedule_id', '=', 'schedules.id')
            ->join('employees', 'employees.id', '=', 'employee_schedules.employee_id')
            ->select('employees.Full_name', 'employee_schedules.id','employee_schedules.Month', 'schedules.Starting_time', 'schedules.Ending_time', 'employee_schedules.available')
            ->where('employee_id', '=', $this->record->id)
            ->where('employee_schedules.available', '=', true)
            ->get()
            ->map(function (Employee_Schedule $task) {
                $isAvailable = $task->available;
                return [
                    'title' => $task->Full_name,
                    'start' => Carbon::parse($task->Month)->format('Y-m-d') . 'T' . Carbon::parse($task->Starting_time)->format('H:i:s'),
                    'end' => Carbon::parse($task->Month)->format('Y-m-d') . 'T' . Carbon::parse($task->Ending_time)->format('H:i:s'),
                    'color' => 'rgb(0,100,0)',
                    'url' => EmployeeScheduleResource::getUrl(name: 'view', parameters: ['record' => $task->id]),
                ];
            })
            ->toArray();
        return array_merge($employee_available,$employee_not_available);
    }
    public static function canView(): bool
    {
        return false;
    }

}
