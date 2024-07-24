<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar; 
use Filament\Actions;
use Filament\Resources\Pages\EditRecord; 

class EditEmployee extends EditRecord
{
    use HasPageSidebar;
    protected static string $resource = EmployeeResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [ 
            Actions\DeleteAction::make(),
        ];
    }
}
