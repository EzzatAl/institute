<?php

namespace App\Filament\Resources\PlacementTestResource\Pages;

use App\Filament\Resources\PlacementTestResource;
use App\Models\PlacementTest;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;

class ViewPlacementTests extends ViewRecord
{
    protected static string $resource = PlacementTestResource::class;
    //public PlacementTest $placementTest;

    protected function getFormSchema(): array
    {
        return [
            Select::make('employee_id')
                ->relationship('employee','Full_name')
                ->required()
                ->preload()
                ->placeholder("Choose the employee")
                ->native(false),
            Select::make('schedule_id')
                ->relationship('schedule','full_schedule')
                ->preload()
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
                ->preload()
                ->placeholder("Choose employee's Month")
                ->native(false),
            Select::make('Day')
                ->options([
                    "SUNDAY" => 'Sunday',"MONDAY" => 'Monday',"TUESDAY" => 'Tuesday',"WEDNESDAY" => 'Wednesday',
                    "THURSDAY" => 'Thursday',"FRIDAY" => 'Friday',"SATURDAY" => 'Saturday',
                ])
                ->required()
                ->preload()
                ->placeholder("Choose employee's Day")
                ->native(false),
        ];
    }
}
