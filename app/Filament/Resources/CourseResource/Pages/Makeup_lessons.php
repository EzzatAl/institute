<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Session;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class Makeup_lessons extends Page implements HasTable
{
    use InteractsWithTable, HasPageSidebar;
    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.resources.course-resource.pages.makeup_lessons';
    public Course $record;
    public function getTableQuery(): Builder
    {
        return GroupStudent::query()
            ->join('groups', 'group_students.group_id', '=', 'groups.id')
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->where('courses.id', '=', $this->record->id)
            ->select('group_students.*');
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('group.Course')
                ->label('Course')
                ->sortable(),
            TextColumn::make('group.Group_number')
                ->label('Group Number')
                ->sortable()
                ->badge(),
            TextColumn::make('student.Berlitz_NAME')
                ->sortable(),
            TextColumn::make('Reason'),
            TextColumn::make('Mark')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
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
}
