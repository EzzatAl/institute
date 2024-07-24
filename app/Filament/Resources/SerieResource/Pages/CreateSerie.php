<?php

namespace App\Filament\Resources\SerieResource\Pages;

use App\Filament\Resources\SerieResource;
use App\Filament\Resources\SubjectResource;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class CreateSerie extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = SerieResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        $formData = $this->data;
        $levelIds = $formData['level_id'];
                    foreach ($levelIds as $levelId) {
                        $serie = SerieResource::getModel()::make();
                        $serie->level_Id = $levelId;
                        $serie->subject_id = $formData['subject_id'];
                        $serie->Primary_Series = $formData['Primary_Series'];
                        $serie->category = $formData['category'];
                        $serie->starting_age =  $formData['starting_age'];
                        $serie->ending_age = $formData['ending_age'];
                        $serie->save();
                    }

        return SerieResource::getModel()::make();

}
protected function getSteps(): array
{
    return [
                Step::make('Subject')
                    ->description('Select the Subject')
                    ->schema([
                Select::make('subject_id')
                ->relationship('subject', 'serie')
                ->required()
                ->searchable()
                ->preload()
                ->placeholder('Select the Subject')
                ->native(false),
                Checkbox::make('Primary_Series'),
            ]),
                Step::make('Level')
                    ->description("Select serie's levels")
                    ->schema([
                Select::make('level_id')
                ->relationship('level', 'Number_latter')
                ->multiple()
                ->preload()
                ->searchable()
                ->placeholder("Choose subject's levels")
                ->native(false)
                ->required(),
            ]),
                Step::make('Category')
                    ->description('Kids or Adults')
                    ->schema([
                Select::make('category')
                    ->options([
                    "KIDS" => 'Kids',"ADULTS"=> 'Adults'])
                    ->required()
                    ->preload()
                    ->placeholder("Choose serie's category")
                    ->native(false),
            ]),
                Step::make('Starting Age')
                ->schema([
                TextInput::make('starting_age')
                ->maxLength(255)
                ->numeric()
                ->minValue(4)
                ->required(),
            ]),
                Step::make('Ending Age')
                    ->schema([
                TextInput::make('ending_age')
                ->maxLength(255)
                ->numeric()
                ->minValue(4)
                ->required(),
            ]),
    ];
}
protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
