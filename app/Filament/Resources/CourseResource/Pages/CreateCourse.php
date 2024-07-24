<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Enums\CourseStatus;
use App\Enums\CourseType;
use App\Filament\Resources\CourseResource;
use App\Models\Course;
use App\Models\ClassroomSchedules;
use App\Models\Schedule;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /*protected function handleRecordCreation(array $data): Model
    {
        $formData = $this->data;
        $schedules = $formData['course_time'];
        $schedulesId = [];

        foreach ($schedules as $schedule) {
            $timeParts = explode(" ", $schedule);
            $scheduleId = Schedule::query()->where('schedule_name', '=', $timeParts[0])->pluck('id')->first();
            $schedulesId[] = $scheduleId;
        }

        $course = Course::query()->create($data);

        foreach ($schedulesId as $scheduleId) {
            ClassroomSchedules::query()->create([
                'course_id' => $course->id,
                'schedule_id' => $scheduleId
            ]);
        }

        return $course;
    }*/
}
