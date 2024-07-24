<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\GroupStudent;
use App\Models\RegisterCourse;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListStudents extends ListRecords 
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            null => Tab::make('All')
                ->badge(Student::query()->count()),
            'Active' => Tab::make()->query(function ($query) {
                $studentIds = GroupStudent::query()
                    ->join('groups', 'groups.id', '=', 'group_students.group_id')
                    ->where('groups.Ending_Date','>=',Carbon::now()->format('Y-m-d'))
                    ->select('group_students.student_id')
                    ->pluck('student_id');
                return $query->whereIn('id', $studentIds);
            })
                ->icon('heroicon-m-check-badge')
                ->badgeColor('success')
                ->badge(GroupStudent::query()
                    ->join('groups', 'groups.id', '=', 'group_students.group_id')
                    ->where('groups.Ending_Date','>=',Carbon::now()->format('Y-m-d'))->count()
                ),
            'Pending' => Tab::make()->query(function ($query) {
            $studentIds = RegisterCourse::query()
                ->join('courses', 'courses.id', '=', 'register_courses.course_id')
                ->where('courses.Starting_Date','>=',Carbon::now()->format('Y-m-d'))
                ->select('register_courses.student_id')
                ->pluck('student_id');
            return $query->whereIn('id', $studentIds);
        })
                ->icon('heroicon-m-exclamation-triangle')
                ->badgeColor('warning')
                ->badge(RegisterCourse::query()
                    ->join('courses', 'courses.id', '=', 'register_courses.course_id')
                    ->where('courses.Starting_Date','>=',Carbon::now()->format('Y-m-d'))->count()
                ),
            'InActive' => Tab::make()->query(function ($query) {
                $fifteenDaysAgo = Carbon::now()->subDays(15);
                $studentIds = GroupStudent::query()
                    ->join('groups', 'groups.id', '=', 'group_students.group_id')
                    ->where('groups.Ending_Date', '<=', $fifteenDaysAgo)
                    ->pluck('group_students.student_id');
                $inactiveStudentIds = $studentIds->diff(
                    RegisterCourse::query()
                        ->whereIn('student_id', $studentIds)
                        ->pluck('student_id')
                );
                return $query->whereIn('id', $inactiveStudentIds);
            })
                ->icon('heroicon-m-x-circle')
                ->badgeColor('danger')
                ->badge(function () {
                    $fifteenDaysAgo = Carbon::now()->subDays(15);
                    $studentIds = GroupStudent::query()
                        ->join('groups', 'groups.id', '=', 'group_students.group_id')
                        ->where('groups.Ending_Date', '<=', $fifteenDaysAgo)
                        ->pluck('group_students.student_id');
                    $inactiveStudentIds = $studentIds->diff(
                        RegisterCourse::query()
                            ->whereIn('student_id', $studentIds)
                            ->pluck('student_id')
                    );

                    // Return the count of inactive students
                    return $inactiveStudentIds->count();
                }),
        ];
    }
}
