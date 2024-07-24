<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;



class ListExams extends ListRecords

{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
