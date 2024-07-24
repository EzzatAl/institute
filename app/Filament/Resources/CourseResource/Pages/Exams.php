<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Enums\Evaluationn;
use App\Filament\Resources\CourseResource;
use App\Models\Level;
use App\Models\Serie;
use Filament\Resources\Pages\Page;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\KidsExam;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Doctrine\DBAL\Schema\View;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class Exams extends Page implements HasTable
{
    use InteractsWithTable, HasPageSidebar;

    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.resources.course-resource.pages.exams';
    public Course $record;

    public function getTableQuery(): Builder
    {
        $group = Group::query()->where('course_id', $this->record->id)->first();
        $groupStudents = GroupStudent::query()->where('group_id', $group->id)->pluck('id')->toArray();
        $serie = Serie::query()->where('id','=',$this->record->serie_id)->first();
        if($serie->category == 'ADULTS')
        {
            return Exam::query()->whereIn('group_student_id', $groupStudents);
        }
        if($serie->category == 'KIDS')
        {
           return KidsExam::query()->whereIn('group_student_id', $groupStudents);
        }
    }


    public function getTableColumns(): array
{
    $course = Course::query()->find($this->record->id);
    $serie = Serie::query()->find($course->serie_id);
    $level = Level::query()->find($serie->level_id);
    if($serie->category == 'ADULTS')
        {
            if (in_array($level->Number, [9, 10])) {
            // Specific form for levels 9 and 10
            return [
                TextColumn::make('student.Berlitz_NAME'),
                TextColumn::make('exam_type'),
                TextColumn::make('Written_Test'),
                TextColumn::make('Oral_Test'),
                TextColumn::make('Attendance'),
                TextColumn::make('Participation'),
                TextColumn::make('Home_Work'),
                TextColumn::make('Mark'),
            ];
        } else {
            return [
                TextColumn::make('student.Berlitz_NAME'),
                TextColumn::make('exam_type'),
                TextColumn::make('Written_Test')
                    ->visible($level->test_type == 'Written'),
                TextColumn::make('Oral_Test')
                    ->visible($level->test_type == 'Written'),
                TextColumn::make('Attendance')
                    ->visible($level->test_type == 'Written'),
                TextColumn::make('Participation')
                    ->visible($level->test_type == 'Written'),
                TextColumn::make('Home_Work')
                    ->visible($level->test_type == 'Written'),
                TextColumn::make('Communication')
                    ->visible($level->test_type == 'Oral'),
                TextColumn::make('Vocabulary')
                    ->visible($level->test_type == 'Oral'),
                TextColumn::make('Structure')
                    ->visible($level->test_type == 'Oral'),
                TextColumn::make('Mark'),
            ];
        }
        }
        if($serie->category == 'KIDS')
        {
            return [
                TextColumn::make('student.Berlitz_NAME'),
                TextColumn::make('Communication'),
                TextColumn::make('Vocabulary'),
                TextColumn::make('Structure'),
            ];
        }

}

public function getTableHeaderActions(): array
{
    $course = Course::query()->find($this->record->id);
    $serie = Serie::query()->find($course->serie_id);
    $level = Level::query()->find($serie->level_id);

    $group = Group::query()->where('course_id', $this->record->id)->first();
    $groupStudents = GroupStudent::query()->where('group_id', $group->id)->pluck('id')->toArray();

    return [
        CreateAction::make()
            ->label('Student Exam')
            ->form(function (Form $form) use ($serie, $level, $groupStudents) {
                if ($serie->category === 'ADULTS') {
                    // Adults series schema
                    return $form->schema([
                        Select::make('group_student_id')
                            ->relationship('student', 'Berlitz_NAME') // Use 'Berlitz_NAME' as the display column
                            ->options(function () use ($groupStudents) {
                                return GroupStudent::query()
                                    ->whereIn('group_students.id', $groupStudents) // Specify the table name for 'id' column
                                    ->join('students', 'students.id', '=', 'group_students.student_id')
                                    ->pluck('students.CONCAT_NAME_RANDOM', 'group_students.id'); // Fetch Berlitz_NAME for display
                            })
                            ->required()
                            ->native(false),
                        Hidden::make('exam_type')
                            ->default($level->test_type)
                            ->required(),
                        TextInput::make('Written_Test')
                            ->visible($level->test_type === 'Written')
                            ->required()
                            ->numeric(),
                        TextInput::make('Oral_Test')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Written'),
                        TextInput::make('Attendance')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Written'),
                        TextInput::make('Participation')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Written'),
                        TextInput::make('Home_Work')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Written'),
                        TextInput::make('Communication')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Oral'),
                        TextInput::make('Vocabulary')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Oral'),
                        TextInput::make('Structure')
                            ->required()
                            ->numeric()
                            ->visible($level->test_type === 'Oral'),
                        TextInput::make('Mark')
                            ->required()
                            ->default(0)
                            ->numeric(),
                    ]);
                } else {
                    // Kids series schema
                    return $form->schema([
                        Select::make('group_student_id')
                            ->relationship('student', 'Berlitz_NAME') // Use 'Berlitz_NAME' as the display column
                            ->options(function () use ($groupStudents) {
                                return GroupStudent::query()
                                    ->whereIn('group_students.id', $groupStudents) // Specify the table name for 'id' column
                                    ->join('students', 'students.id', '=', 'group_students.student_id')
                                    ->pluck('students.CONCAT_NAME_RANDOM', 'group_students.id'); // Fetch Berlitz_NAME for display
                            })
                            ->required()
                            ->native(false),
                        ToggleButtons::make('Communication')
                            ->required()
                            ->inline()
                            ->options(Evaluationn::class),
                        ToggleButtons::make('Vocabulary')
                            ->required()
                            ->inline()
                            ->options(Evaluationn::class),
                        ToggleButtons::make('Structure')
                            ->required()
                            ->inline()
                            ->options(Evaluationn::class),
                    ]);
                }
            })
            ->action(function (array $data) use ($serie) {
                if ($serie->category === 'ADULTS') {
                    // Handle form submission for adults
                    Exam::create([
                        'group_student_id' => $data['group_student_id'],
                        'exam_type' => $data['exam_type'],
                        'Written_Test' => $data['Written_Test'] ?? null,
                        'Oral_Test' => $data['Oral_Test'] ?? null,
                        'Attendance' => $data['Attendance'] ?? null,
                        'Participation' => $data['Participation'] ?? null,
                        'Home_Work' => $data['Home_Work'] ?? null,
                        'Communication' => $data['Communication'] ?? null,
                        'Vocabulary' => $data['Vocabulary'] ?? null,
                        'Structure' => $data['Structure'] ?? null,
                        'Mark' => $data['Mark'],
                    ]);
                } else {
                    // Handle form submission for kids
                    KidsExam::create([
                        'group_student_id' => $data['group_student_id'],
                        'Communication' => $data['Communication'],
                        'Vocabulary' => $data['Vocabulary'],
                        'Structure' =>  $data['Structure'],
                    ]);
                }
            }),
    ];
}


    public function getTableActions(): array
    {
        return [
            // ViewAction::make()
        ];
    }

    public function getTableBulkActions(): array
    {
        return [
            DeleteBulkAction::make(),

        ];
    }
}
