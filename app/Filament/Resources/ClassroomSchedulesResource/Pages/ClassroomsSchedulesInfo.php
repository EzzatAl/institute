<?php

namespace App\Filament\Resources\ClassroomSchedulesResource\Pages;

use App\Filament\Resources\ClassroomSchedulesResource;
use App\Filament\Resources\EmployeeScheduleResource;
use App\Models\Classroom_Schedules;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ClassroomsSchedulesInfo extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = ClassroomSchedulesResource::class;
    protected static string $view = 'filament.resources.employee-schedule-resource.pages.info';
    public Classroom_Schedules $record;
    public function getTableQuery(): Builder
    {
        return Classroom_Schedules::query()
            ->join('classrooms','classrooms.id','=','classrooms_schedules.classroom_id')
            ->join('sessions','sessions.Day','=','classrooms_schedules.Month')
            ->join('groups','groups.id','=','sessions.group_id')
            ->join('courses','courses.id','=','groups.course_id')
            ->join('series','series.id','=','courses.serie_id')
            ->join('subjects','subjects.id','=','series.subject_id')
            ->join('levels','levels.id','=','series.level_id')
            ->where('classrooms_schedules.id','=',$this->record->id);
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
            TextColumn::make('Class_Number')
                ->label('Class Number'),
            TextColumn::make('Month')
                ->label('Month'),
            TextColumn::make('schedule.full_schedule')
                ->label('Schedule'),
        ];
    }
}
