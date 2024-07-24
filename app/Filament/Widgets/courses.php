<?php

namespace App\Filament\Widgets;
use App\Enums\CourseStatus;
use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Schedule;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class courses extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(Course::query()->where('status','=',CourseStatus::To_Open))
            ->defaultSort('created_at','desc')
            ->columns([
                Tables\Columns\TextColumn::make('serie.series'),
                Tables\Columns\TextColumn::make('course_time')
                ->badge(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('Day')
                    ->badge(),
                Tables\Columns\TextColumn::make('Starting_Date')
                    ->badge(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('course_status')
                ->badge(),
                Tables\Columns\TextColumn::make('created_at'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->form([
         Select::make('serie_id')
                    ->relationship('serie','series')
                    ->preload()
                    ->native(false)
                    ->required(),
                Select::make('course_time')
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
                Select::make('Day')
                    ->options([
                        "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
                        "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
                    ])
                    ->multiple()
                    ->required()
                    ->preload()
                    ->placeholder("Choose Course's Day")
                    ->native(false),
                FileUpload::make('image')
                    ->directory('image')
                    ->image()
                    ->preserveFilenames()
                    ->enableDownload()
                    ->enableOpen(),
                DatePicker::make('Starting_Date')
                    ->required()
                    ->native(false),
                ToggleButtons::make('status')
                    ->inline()
                    ->options(CourseStatus::class)
                    ->default('To Open'),
                ToggleButtons::make('course_status')
                    ->inline()
                    ->options(CourseType::class)
                    ->default('Regular')
                    ->label('Course Type'),
            ]),
                Tables\Actions\EditAction::make()
                    ->form([
                        Select::make('serie_id')
                            ->relationship('serie','series')
                            ->preload()
                            ->native(false)
                            ->required(),
                        Select::make('course_time')
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
                        Select::make('Day')
                            ->options([
                                "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
                                "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
                            ])
                            ->multiple()
                            ->required()
                            ->preload()
                            ->placeholder("Choose Course's Day")
                            ->native(false),
                        FileUpload::make('image')
                            ->directory('image')
                            ->image()
                            ->preserveFilenames()
                            ->enableDownload()
                            ->enableOpen(),
                        DatePicker::make('Starting_Date')
                            ->required()
                            ->native(false),
                        ToggleButtons::make('status')
                            ->inline()
                            ->options(CourseStatus::class)
                            ->default('To Open'),
                        ToggleButtons::make('course_status')
                            ->inline()
                            ->options(CourseType::class)
                            ->default('Regular')
                            ->label('Course Type'),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->record(fn (Course $record) => $record),
            ]);
    }

}
