<?php

namespace App\Filament\Resources\GroupStudentResource\Pages;

use App\Filament\Resources\GroupStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListGroupStudents extends ListRecords
{
    protected static string $resource = GroupStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New Student'),
        ];
    }
}
