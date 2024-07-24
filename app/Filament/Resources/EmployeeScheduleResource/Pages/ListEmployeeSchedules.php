<?php

namespace App\Filament\Resources\EmployeeScheduleResource\Pages;

use App\Filament\Resources\EmployeeScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSchedules extends ListRecords
{
    protected static string $resource = EmployeeScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New Employee Schedule'),
        ];
    }
}
