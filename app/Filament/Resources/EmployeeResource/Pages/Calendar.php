<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\Widgets\CalendarWidget;
use App\Models\Employee;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Resources\Pages\Page;

class Calendar extends Page
{

    use HasPageSidebar;
    protected static string $resource = EmployeeResource::class;
    protected static string $view = 'filament.resources.employee-resource.pages.calendar';
    public Employee $record;

}