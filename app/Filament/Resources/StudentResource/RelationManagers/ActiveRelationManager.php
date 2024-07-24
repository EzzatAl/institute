<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActiveRelationManager extends RelationManager
{
    protected static string $relationship = 'active';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'Course')
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'Berlitz_NAME')
                    ->required(),
                Forms\Components\TextInput::make('Mark')
                    ->required()
                    ->default(0)
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.Course')
                    ->label('Course'),
                Tables\Columns\TextColumn::make('group.Group_number')
                    ->label('Group Number')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('student.Berlitz_NAME'),
                Tables\Columns\TextColumn::make('group.Ending_Date')
                    ->label('Ending Date')
                    ->badge(),
                Tables\Columns\TextColumn::make('Mark')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                ]),
            ]);
    }
}
