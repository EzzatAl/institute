<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeScheduleResource;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Schedule;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class EmployeeInfo extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = EmployeeScheduleResource::class;
    protected static string $view = 'filament.resources.employee-schedule-resource.pages.info';
    public Employee_Schedule $record;
    public function getTableQuery(): Builder
    {
        return Employee_Schedule::query()
            ->join('employees','employees.id','=','employee_schedules.employee_id')
            ->join('sessions','sessions.Day','=','employee_schedules.Month')
            ->join('groups','groups.id','=','sessions.group_id')
            ->join('courses','courses.id','=','groups.course_id')
            ->join('series','series.id','=','courses.serie_id')
            ->join('subjects','subjects.id','=','series.subject_id')
            ->join('levels','levels.id','=','series.level_id')
            ->where('Employee_Schedules.id','=',$this->record->id);
    }
    public function getTableColumns(): array
    {
        return [
            TextColumn::make('series')
                ->label('Series')
                ->default(function ($record) {
                    return $record->Language . ' ' . $record->Type . ' ' . $record->Number_latter;
                }),
            TextColumn::make('Group_number')
                ->label('Group Number'),
            TextColumn::make('Full_name')
                ->label('Full Name'),
            TextColumn::make('Month')
                ->label('Month'),
            TextColumn::make('schedule.full_schedule')
                ->label('Schedule'),
        ];
    }
}
