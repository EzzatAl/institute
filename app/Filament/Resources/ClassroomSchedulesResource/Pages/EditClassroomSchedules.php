<?php

namespace App\Filament\Resources\ClassroomSchedulesResource\Pages;

use App\Filament\Resources\ClassroomSchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassroomSchedules extends EditRecord
{
    protected static string $resource = ClassroomSchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
