<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Filament\Resources\SessionResource;
use Filament\Actions;
use App\Enums\RegisterCourseStatus;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\RegisterCourse;
use App\Models\Session;
use App\Models\Student;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Carbon\Carbon;
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
use Filament\Resources\Pages\ListRecords;

class ListSessions extends ListRecords
{
    protected static string $resource = SessionResource::class;
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
            ->form(function (Form $form) {
        return $form->schema([
            Select::make('employee_id')
                ->options(Employee::query()->pluck('Full_name', 'id'))
                ->preload()
                ->native(false)
                ->required(),
        ]);
    })
    ->action(function (array $data) {
        $employeeId = $data['employee_id'];
    
        // Check if there are any students with "Enrolled" status
        $enrolledStudents = RegisterCourse::query()
            ->where('course_id', '=', $this->record->id)
            ->where('status', '=', 'Enrolled')
            ->get();
    
        $numEnrolledStudents = $enrolledStudents->count();
    
        if ($numEnrolledStudents >= 5 && $numEnrolledStudents <= 15) {
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
                'Number_Of_Units' => 10,
                'Ending_Date' => Carbon::now(),
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
    
            // Calculate the number of sessions to create based on the number of enrolled students
            $numSessions = $numEnrolledStudents <= 5 ? 5 : ($numEnrolledStudents <= 10 ? 10 : 15);
    
            // Create the required number of session records
            $sessions = [];
            for ($i = 0; $i < $numSessions; $i++) {
                $sessionDate = Carbon::now()->addDays($i)->format('Y-m-d');
    
                $session = Session::query()->create([
                    'group_id' => $group->id,
                    'employee_id' => $employeeId,
                    'Day' => $sessionDate,
                    'teacher_Attendance' => true,
                    'Reason' => null,
                    'shifting' => false,
                    'material_covered' => '',
                ]);
    
                $sessions[] = $session;
            }
    
            // Perform any other necessary actions for the session creation
    
            $course = Course::query()->find($this->record->id);
            $course->status = 'Open';
            $course->save();
        } else {
            Notification::make()
                ->title('Insufficient number of enrolled students to create sessions.')
                ->send();
        }
    }),
            CreateAction::make()
                ->label('Register Student')
                ->form(function () {
                    return [

                        Select::make('course_id')
                            ->relationship('course', 'Course')
                            ->preload()
                            ->native(false)
                            ->required(),
//                            ->disabled()
//                            ->default(function () {
//                                return Course::query()->where('id','=', $this->record->id)->first()->id;
//                            }),
                        Select::make('student_id')
                            ->relationship('student', 'Berlitz_NAME')
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
                }),

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
        return [

        ];
    }
    
}
