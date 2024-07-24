<?php

namespace App\Filament\Resources\RegisterCourseResource\Pages;

use App\Filament\Resources\RegisterCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegisterCourse extends EditRecord
{
    protected static string $resource = RegisterCourseResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

}
