<?php

namespace App\Filament\Resources\AudioResource\Pages;

use App\Filament\Resources\AudioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAudio extends CreateRecord
{
    protected static string $resource = AudioResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
   /* protected function handleRecordCreation(array $data): Model
    {
        $formData = $this->data;
        $audios = $formData['audio_path'];
        $audionames = $formData['audio_name'];

        foreach (array_map(null, $audios, $audionames) as [$audio, $audioname]) {
            $serie = AudioResource::getModel()::make();
            $serie->audio_path = $audio;
            $serie->audio_name = $audioname;
            $serie->serie_id = $formData['serie_id'];
            $serie->save();
        }

        return AudioResource::getModel()::make();
    }*/



}
