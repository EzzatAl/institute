<?php

namespace App\Filament\Resources\RegisterCourseResource\Pages;

use App\Filament\Resources\RegisterCourseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\RedirectResponse;

class CreateRegisterCourse extends CreateRecord
{
    protected static string $resource = RegisterCourseResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

}
