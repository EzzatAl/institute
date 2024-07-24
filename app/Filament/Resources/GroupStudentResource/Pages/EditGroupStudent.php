<?php

namespace App\Filament\Resources\GroupStudentResource\Pages;

use App\Filament\Resources\GroupStudentResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGroupStudent extends EditRecord
{
    use HasPageSidebar;
    protected static string $resource = GroupStudentResource::class;

    protected function getHeaderActions(): array 
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
