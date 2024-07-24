<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AudioResource\Pages;
use App\Filament\Resources\AudioResource\RelationManagers;
use App\Models\Audio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class AudioResource extends Resource
{
    protected static ?string $model = Audio::class;

    protected static ?string $navigationIcon = 'heroicon-o-speaker-wave';
    protected static ?string $navigationGroup = "Curriculum Management";
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('serie_id')
                    ->relationship('serie', 'serie')
                    ->preload()
                    ->placeholder("Choose the Serie")
                    ->native(false)
                    ->required()
                    ->preload(),
                    Forms\Components\FileUpload::make('audio_path')
                    ->multiple()
                    ->directory('audios')
                    ->placeholder('Drag & Drop your Audio or Browse')
                    ->storeFileNamesIn('audio_name')
                    ->acceptedFileTypes(['audio/*'])
                    ->preserveFilenames()
                    ->enableReordering()
                    ->enableDownload()
                    ->enableOpen()
                    ->required(),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serie.serie'),
                Tables\Columns\TextColumn::make('audio_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-M-Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAudio::route('/'),
            'create' => Pages\CreateAudio::route('/create'),
            'edit' => Pages\EditAudio::route('/{record}/edit'),
        ];
    }
}
