<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SerieResource\Pages;
use App\Filament\Resources\SerieResource\RelationManagers;
use App\Models\Serie;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SerieResource extends Resource
{
    protected static ?string $model = Serie::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationGroup = "Curriculum Management";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('subject_id')
                    ->relationship('subject', 'serie')
                    ->required()
                    ->preload()
                    ->placeholder('Select the Subject')
                    ->native(false),
                Select::make('level_id')
                    ->relationship('level', 'Number_latter')
                    ->native(false)
                    ->required()
                    ->preload()
                    ->searchable()
                    ->placeholder("Choose subject's levels"),
                Select::make('category')
                    ->options([
                        "KIDS" => 'Kids',"ADULTS"=> 'Adults'])
                    ->required()
                    ->preload()
                    ->placeholder("Choose serie's category")
                    ->native(false),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject.serie')
                ->searchable([
                    "CONCAT(Language, ' ', Type)"
                ]),
                Tables\Columns\TextColumn::make('level.Number_latter')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('Primary_Series'),
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starting_age')
                    ->label('Starting Age')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ending_age')
                    ->label('Ending Age')
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSeries::route('/'),
            'create' => Pages\CreateSerie::route('/create'),
            'edit' => Pages\EditSerie::route('/{record}/edit'),
        ];
    }
}
