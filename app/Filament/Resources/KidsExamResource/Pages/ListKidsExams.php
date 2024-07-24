<?php

namespace App\Filament\Resources\KidsExamResource\Pages;

use App\Filament\Resources\KidsExamResource;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupStudent;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;

class ListKidsExams extends ListRecords
{

    protected static string $resource = KidsExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
}
