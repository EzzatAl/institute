<?php

namespace App\Filament\Resources\EmployeeScheduleResource\Pages;

use App\Filament\Resources\EmployeeScheduleResource;
use App\Models\Employee_Schedule;
use Carbon\Carbon;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEmployeeSchedule extends CreateRecord
{
    protected static string $resource = EmployeeScheduleResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        $formData = $this->data;
        $scheduleIds = $formData['schedule_id'];
        $Months = $formData['Month'];
        $Days = $formData['Day'];

        foreach ($scheduleIds as $scheduleId) {
            foreach ($Months as $Month) {
                foreach ($Days as $Day) {
                    $dates = [];
                    $year = Carbon::now()->year;
                    $startDate = Carbon::create($year, $Month, 1)->startOfMonth();
                    $endDate = Carbon::create($year, $Month, 1)->endOfMonth();

                    while ($startDate->lte($endDate)) {
                        if ($startDate->isDayOfWeek($Day)) {
                            $dates[] = $startDate->format('Y-m-d');
                        }
                        $startDate->addDay();
                    }

                    foreach ($dates as $date) {
                        $existingRecord = Employee_Schedule::query()
                            ->where('employee_id', $formData['employee_id'])
                            ->where('schedule_id','=',$scheduleId)
                            ->where('Month', $date)
                            ->where('Day', $Day)
                            ->first();
                        if (!$existingRecord) {
                            $employeeSchedule = EmployeeScheduleResource::getModel()::make();
                            $employeeSchedule->employee_id = $formData['employee_id'];
                            $employeeSchedule->schedule_id = $scheduleId;
                            $employeeSchedule->Month = $date;
                            $employeeSchedule->Day = $Day;
                            $employeeSchedule->save();
                        }
                    }
                }
            }
        }

        return EmployeeScheduleResource::getModel()::make();
    }

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Select::make('employee_id')
            ->relationship('employee','Full_name')
            ->required()
            ->preload()
            ->placeholder("Choose the employee")
            ->native(false),
        Select::make('schedule_id')
        ->relationship('schedule','full_schedule')
        ->preload()
        ->multiple()
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
        ->multiple()
        ->preload()
        ->placeholder("Choose employee's Month")
        ->native(false),
        Select::make('Day')
        ->options([
            "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
            "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
        ])
        ->multiple()
        ->required()
        ->preload()
        ->placeholder("Choose employee's Day")
        ->native(false),
        ]);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}

