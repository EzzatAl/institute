<?php

namespace App\Filament\Resources\SessionResource\Pages;

use App\Filament\Resources\SessionResource;
use App\Models\Attendance;
use App\Models\Classroom_Schedules;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Employee_Schedule;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Schedule;
use App\Models\Session;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSession extends CreateRecord
{
    protected static string $resource = SessionResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function handleRecordCreation(array $data): Model
    {
        $formData = $this->data;
        $data_time = $formData['Day'];
        $Unit = $formData['Unit'];
        $group_id = $formData['group_id'];
        $employee_id = $formData['employee_id'];
        $group = Group::query()->where('id','=',$group_id)->first();
        $course = Course::query()
            ->join('groups', 'groups.course_id', '=', 'courses.id')
            ->where('groups.id', '=', $group_id)
            ->first();
        $schedules = $course->course_time;
        $employee = Employee::query()->where('id', '=', $employee_id)->first();
        $schedule_employees = Employee_Schedule::query()->where('Month', '=', $data_time)
            ->where('employee_id', '=', $employee_id)->get();
        $notAvailable = false;

        if ($schedule_employees->isEmpty()) {
            Notification::make()
                ->title("This teacher {$employee->Full_name} doesn't have a schedule on this date {$data_time}")
                ->send();
        } else {
            foreach ($schedule_employees as $schedule_employee) {
                foreach ($schedules as $schedule) {
                    $time_parts = explode(" ", $schedule);
                    $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                    if ($schedule_employee->schedule_id == $scheduleId) {
                        if (!$schedule_employee->available) {
                            $notAvailable = true;
                        }
                    }
                }
            }

            if ($notAvailable) {
                Notification::make()
                    ->title("This teacher {$employee->Full_name} is not available on this date {$data_time}")
                    ->send();
            } else {
                foreach ($schedule_employees as $schedule_employee) {
                    foreach ($schedules as $schedule) {
                        $time_parts = explode(" ", $schedule);
                        $scheduleId = Schedule::query()->where('schedule_name', '=', $time_parts[0])->value('id');
                        if ($schedule_employee->schedule_id == $scheduleId) {
                            $schedule_employee->available = false;
                            $schedule_employee->save();
                            $classroom = Classroom_Schedules::query()->create([
                                'classroom_id'=>$group->classroom_id,
                                'schedule_id'=>$scheduleId,
                                'Month'=>$data_time,
                                'Day'=>Carbon::parse($data_time)->format('l'),
                                'available'=>false
                            ]);
                        }
                    }
                }
                $session = SessionResource::getModel()::make();
                $session->group_id = $group_id;
                $session->employee_id = $employee_id;
                $session->Day = $data_time;
                $session->teacher_Attendance = false;
                $session->Reason = null;
                $session->shifting = false;
                $session->material_covered = null;
                $session->Unit = $Unit;
                $session->save();
                $group = Group::query()->where('id','=',$group_id)->first();
                $group->Number_Of_Units += $Unit;
                $group->Ending_Date = $data_time;
                $group->save();
            }
        }
        return SessionResource::getModel()::make();
    }
    protected function getCreatedNotificationTitle(): ?string
    {

        return " ";
    }
    protected function afterCreate(): void
    {
        $session = $this->record;
        $group = Group::query()
            ->join('sessions','sessions.group_id','=','groups.id')
            ->where('sessions.id','=',$session->id);
        $group->Ending_Date = $session->Day;
//        $GroupStudents = GroupStudent::query()->where('group_id','=',$session->group_id)->get();
//
//        foreach ($GroupStudents as $groupStudent)
//        {
//            Attendance::query()->create([
//                'session_id'=>$session->id,
//                'group_student_id'=> $groupStudent->id,
//                'status'=>'Absent',
//                'Notes'=>null
//            ]);
//        }

    }
}
