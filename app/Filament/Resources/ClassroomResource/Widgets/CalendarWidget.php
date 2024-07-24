<?php

namespace App\Filament\Resources\ClassroomResource\Widgets;

use App\Filament\Resources\ClassroomSchedulesResource;
use App\Models\Classroom_Schedules;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | int | string | null $record;
    public function fetchEvents(array $fetchInfo): array
    {
        return Classroom_Schedules::query()
            ->join('schedules', 'classrooms_schedules.schedule_id', '=', 'schedules.id')
            ->where('classroom_id', '=', $this->record->id)
            ->get()
            ->map(function (Classroom_Schedules $task) {
                $isAvailable = $task->available;
                return [
                    'title' => $isAvailable ? 'Available' : 'Not Available',
                    'start' => Carbon::parse($task->Month)->format('Y-m-d') . 'T' . Carbon::parse($task->Starting_time)->format('H:i:s'),
                    'end' => Carbon::parse($task->Month)->format('Y-m-d') . 'T' . Carbon::parse($task->Ending_time)->format('H:i:s'),
                    'color' => 'rgb(139,0,0)',
                    'url'=> ClassroomSchedulesResource::getUrl(name: 'info', parameters: ['record' => $task->id]),
                ];
            })
            ->toArray();
    }
    public static function canView(): bool
    {
        return false;
    }

}
