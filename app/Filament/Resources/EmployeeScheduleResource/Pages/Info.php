<?php

namespace App\Filament\Resources\EmployeeScheduleResource\Pages;

use App\Filament\Resources\EmployeeScheduleResource;
use App\Models\Course;
use App\Models\Employee_Schedule;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Level;
use App\Models\Serie;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class Info extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = EmployeeScheduleResource::class;
    protected static string $view = 'filament.resources.employee-schedule-resource.pages.info';
    public Employee_Schedule $record;
    public function getTableQuery(): Builder
    {
        return Employee_Schedule::query()->where('id','=',$this->record->id);
    }
    public function getTableColumns(): array
    {

        return [
            TextColumn::make('employee.Full_name')
                ->searchable(),
            TextColumn::make('schedule.full_schedule'),
            TextColumn::make('Month')
                ->searchable(),
            TextColumn::make('Day')
                ->searchable(),
        ];
    }
}
