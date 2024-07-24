<?php

namespace App\Filament\Resources;

use App\Enums\CourseStatus;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use App\Enums\CourseType;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Filament\Resources\ExamResource\Pages\ListExams;
use App\Filament\Resources\GroupStudentResource\Pages\ListGroupStudents;
use App\Models\Course;
use App\Models\ClassroomSchedules;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Schedule;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;


class CourseResource extends Resource
{
    
    protected static ?string $model = Course::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function sidebar(Model $record): FilamentPageSidebar
    {

        return FilamentPageSidebar::make()
            ->setTitle($record->serie['series'])
            ->setDescription($record->created_at)
            ->sidebarNavigation()
            ->setNavigationItems([
                PageNavigationItem::make('View Course')
                    ->url(function () use ($record) {
                        return static::getUrl('view', ['record' => $record->id]);
                    }),
                PageNavigationItem::make('Edit Course')
                    ->url(function () use ($record) {
                        return static::getUrl('edit', ['record' => $record->id]);
                    })->visible($record->status == CourseStatus::To_Open),
                PageNavigationItem::make('Registered Students')
                    ->url(function () use ($record) {
                        return static::getUrl('register', ['record' => $record->id]);
                    })->visible($record->status == CourseStatus::To_Open),
                    PageNavigationItem::make('Group Students')
                    ->url(function () use ($record) {
                        $group = Group::query()->where('course_id', '=', $record->id)->first();
                        session(['group_id' => $group->id]);
                        return GroupStudentResource::getUrl('index');
                    })
                    ->visible(in_array($record->status, [CourseStatus::Open, CourseStatus::Finished])),                
                    PageNavigationItem::make("Student's Exam")
                    ->url(function () use ($record) {
                        return static::getUrl('exam', ['record' => $record->id]);
                    })
                    ->visible(in_array($record->status, [CourseStatus::Open, CourseStatus::Finished])),
            ]);
    }public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('serie_id')
                ->relationship('serie','series')
                ->preload()
                ->native(false)
                ->required(),
            Forms\Components\Select::make('course_time')
                ->options(Schedule::query()
                    ->pluck('schedule_name', 'schedule_name')
                    ->mapWithKeys(function ($name, $key) {
                            $schedule = Schedule::query()->where('schedule_name', $name)->first();
                            $value = $name . ' ' . $schedule->Starting_time_with_AM_PM . ' ' . $schedule->Ending_time_with_AM_PM;
                            return [$value => $value];
                        })
                    ->toArray()
                )
                ->label('Course Time')
                ->multiple()
                ->minItems(2)
                ->maxItems(3)
                ->required()
                ->native(false),
            Forms\Components\Select::make('Day')
                ->options([
                    "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
                    "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
                ])
                ->multiple()
                ->required()
                ->preload()
                ->placeholder("Choose Course's Day")
                ->native(false),
            Forms\Components\FileUpload::make('image')
                ->directory('image')
                ->image()
                ->preserveFilenames()
                ->enableDownload()
                ->enableOpen(),
            Forms\Components\DatePicker::make('Starting_Date')
                ->required()
                ->native(false),
            Forms\Components\ToggleButtons::make('status')
                ->inline()
                ->options(CourseStatus::class)
                ->default('To Open'),
            Forms\Components\ToggleButtons::make('course_status')
                ->inline()
                ->options(CourseType::class)
                ->default('Regular')
                ->label('Course Type'),

        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serie.series')
                    ->numeric(),
                Tables\Columns\TextColumn::make('course_time')
                    ->badge(),
//                    ->inline(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('course_status')
                    ->badge()
                    ->label('Course Type'),
                Tables\Columns\TextColumn::make('Day')
                    ->searchable()
                    ->label('Days') 
                    ->badge(),
                Tables\Columns\TextColumn::make('Starting_Date')
                    ->date(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('Starting_Date','asc')
            ->filters([
                //
            ])
            ->actions([
                ActionsActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('Setting')
                        ->label('Setting')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->url(function ($record) {
                            $group = Group::query()->where('course_id', '=', $record->id)->first();
                            if($group) {
                                return static::getUrl('setting', ['record' => $group->id]);
                            }
                            return null;
                        })
                        ->visible(function ($record) {
                            return in_array($record->status, [CourseStatus::Open, CourseStatus::Finished]);
                        }),
                    Tables\Actions\Action::make("Session")
                        ->icon('heroicon-o-clipboard-document-check')
                        ->action(function ($record) {
                            $group = Group::query()->where('course_id', '=', $record->id)->first();
                            if ($group) {
                                session(['group_id' => $group->id]);
                                $url = SessionResource::getUrl('index');
                                return redirect($url);
                            }
                            return null;
                        })->visible(function ($record) {
                            return in_array($record->status, [CourseStatus::Open, CourseStatus::Finished]);
                        }),
                    Tables\Actions\Action::make("Closing Registration")
                        ->icon('heroicon-o-lock-closed')
                        ->action(function ($record) {
                            $course = Course::find($record->id);
                            if ($course) {
                                $course->update(['Lock_course' => true]);
                            }
                        })
                        ->visible(function ($record) {
                            return !$record->Lock_course;
                        })
                        ->hidden(function ($record)
                        {
                            return in_array($record->status, [CourseStatus::Open, CourseStatus::Finished]);
                        }),
                    Tables\Actions\Action::make("Opening Registration")
                        ->icon('heroicon-o-lock-open')
                        ->action(function ($record) {
                            $course = Course::find($record->id);
                            if ($course) {
                                $course->update(['Lock_course' => false]);
                            }
                        })
                        ->visible(function ($record) {
                            return $record->Lock_course;
                        })
                        ->hidden(function ($record)
                        {
                            return in_array($record->status, [CourseStatus::Open, CourseStatus::Finished]);
                        }),
                        Tables\Actions\Action::make("Announcing")
                        ->visible(function ($record)
                        {
                            return $record->status == CourseStatus::To_Open &&  $record->Announcing == false ;
                        })
                        ->hidden(function ($record)
                        {
                            return in_array($record->status, [CourseStatus::Open, CourseStatus::Finished]);
                        })
                        ->action(function($record)
                        {
                            $course = Course::query()->find($record->id);
                            if ($course) {
                                $course->update(['Announcing' => true]);
                            }
                        })
                        ->icon('heroicon-o-arrow-up-on-square-stack'),

                        Tables\Actions\Action::make("UnAnnouncing")
                        ->visible(function ($record)
                        {
                            return $record->status == CourseStatus::To_Open &&  $record->Announcing == true ;
                        })
                        ->hidden(function ($record)
                        {
                            return in_array($record->status, [CourseStatus::Open, CourseStatus::Finished]);
                        })
                        ->action(function($record)
                        {
                            $course = Course::query()->find($record->id);
                            if ($course) {
                                $course->update(['Announcing' => false]);
                            }
                        })
                        ->icon('heroicon-o-arrow-up-on-square-stack'),
            ])
                    ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => CourseResource\Pages\ListCourses::route('/'),
            'create' => CourseResource\Pages\CreateCourse::route('/create'),
            'edit' => CourseResource\Pages\EditCourse::route('/{record}/edit'),
            'view' => CourseResource\Pages\ViewCourse::route('/{record}/view'),
            'register'=> CourseResource\Pages\ListRegisterCourses::route('/{record}/register'),
            'setting'=> GroupResource\Pages\EditGroup::route('/{record}/setting'),
            'exam'=> CourseResource\Pages\Exams::route('/{record}/exam'),
            'make-up'=> CourseResource\Pages\Makeup_lessons::route('/{record}/make-up'),
        ];
    }
}
