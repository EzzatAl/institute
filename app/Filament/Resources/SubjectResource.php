<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Serie;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationGroup = "Curriculum Management";
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Language')
                ->required()
                ->rules(['regex:/^[a-zA-Z]+$/'])
                ->maxLength(255),            
                Forms\Components\TextInput::make('Type')
                    ->required()
                    ->rules(['regex:/^[a-zA-Z0-9]+$/'])
                    ->maxLength(255),
                Forms\Components\Select::make('pre_condition')
                    ->options(function () {
                    return Serie::query()
                    ->selectRaw("CONCAT(subjects.Language, ' ', subjects.Type, ' ', levels.Number_latter) AS option_label")
                    ->where('Primary_Series', '=', 1)
                    ->join('levels', 'levels.id', '=', 'series.level_id')
                    ->join('subjects', 'subjects.id', '=', 'series.subject_id')
                    ->get()
                    ->pluck('option_label','option_label');
                    })
                    ->searchable()
                    ->placeholder("Select the Required level")
                    ->native(false)
                    ->default('No pre-condition exists')
                    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Language') 
                    ->searchable(),
                Tables\Columns\TextColumn::make('Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pre_condition')
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
