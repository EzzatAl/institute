<?php

namespace App\Filament\Resources\GroupStudentResource\Pages;

use App\Filament\Resources\GroupStudentResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupStudent extends ViewRecord
{
    use HasPageSidebar;
    protected static string $resource = GroupStudentResource::class;
}
