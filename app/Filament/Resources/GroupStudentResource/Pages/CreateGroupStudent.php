<?php

namespace App\Filament\Resources\GroupStudentResource\Pages;

use App\Filament\Resources\GroupStudentResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGroupStudent extends CreateRecord
{
    protected static string $resource = GroupStudentResource::class;

}

