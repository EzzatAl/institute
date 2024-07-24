<?php

namespace App\Filament\Resources\PlacementTestResource\Pages;

use App\Filament\Resources\PlacementTestResource;
use App\Models\Shop\Order;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreatePlacementTest extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected static string $resource = PlacementTestResource::class;        
}