<?php

namespace App\Filament\Resources\ClassroomSchedulesResource\Pages;

use App\Filament\Resources\ClassroomSchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassroomSchedules extends ListRecords
{
    protected static string $resource = ClassroomSchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
