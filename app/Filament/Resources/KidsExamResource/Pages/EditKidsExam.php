<?php

namespace App\Filament\Resources\KidsExamResource\Pages;

use App\Filament\Resources\KidsExamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKidsExam extends EditRecord
{
    protected static string $resource = KidsExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
