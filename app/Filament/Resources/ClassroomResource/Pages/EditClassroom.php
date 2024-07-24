<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Filament\Resources\ClassroomResource;
use App\Models\Group;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditClassroom extends EditRecord
{
    use HasPageSidebar;
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function beforeSave(): void
    {
        $group = Group::query()->where('classroom_id','=',$this->getRecord()->id)
            ->where('Ending_Date','>=',Carbon::now())->exists();
        if($group)
        {
            Notification::make()
                ->title("The class is currently associated with a special activity of the institute.")
                ->send();
        }
    }
}
