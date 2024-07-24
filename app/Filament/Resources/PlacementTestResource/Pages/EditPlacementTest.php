<?php

namespace App\Filament\Resources\PlacementTestResource\Pages;

use App\Filament\Resources\PlacementTestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord; 

class EditPlacementTest extends EditRecord
{
    protected static string $resource = PlacementTestResource::class;
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
