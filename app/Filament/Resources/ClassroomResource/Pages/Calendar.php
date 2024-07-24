<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Filament\Resources\ClassroomResource;
use App\Models\Classroom;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Resources\Pages\Page;

class Calendar extends Page
{

    use HasPageSidebar;
    protected static string $resource = ClassroomResource::class;
    protected static string $view = 'filament.resources.classroom-resource.pages.calendar';
    public Classroom $record;

}
