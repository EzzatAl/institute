<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = RoleResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    } 
    protected function getSteps(): array
    {
        return [
            Step::make('Role Name')
                ->description('Give the Role a clear and unique name')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->live()
                ]),
            Step::make('Permission')
                ->description('Select All Permissions For The Role')
                ->schema([
                    MultiSelect::make('Permission')
                        ->relationship('permissions','name')
                        ->preload()
                ]),
        ];
    }
}
