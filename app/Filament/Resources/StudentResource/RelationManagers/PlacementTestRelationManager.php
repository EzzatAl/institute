<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Enums\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class PlacementTestRelationManager extends RelationManager
{
    protected static string $relationship = 'placement_test';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                Tables\Columns\TextColumn::make('formatted_name')
                        ->label('Student Name')
                        ->searchable(),
                Tables\Columns\TextColumn::make('formatted_employee')
                        ->label('Moderator'),
                Tables\Columns\TextColumn::make('formatted_course')
                        ->label('Subject'),
                    Tables\Columns\TextColumn::make('formatted_level')
                    ->label('Level'),
                Tables\Columns\TextColumn::make('formatted_email')
                    ->searchable(),
                ]),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('formatted_status')
                                ->badge(),
                    Tables\Columns\TextColumn::make('formatted_phonenumber')
                                ->searchable(),
                    Tables\Columns\TextColumn::make('formatted_homenumber')
                                ->searchable(),
                    Tables\Columns\TextColumn::make('formatted_notes'),
                    Tables\Columns\TextColumn::make('formatted_date'),
                    ])->collapsible(),
            ])
            ->filters([
                //
            ])
            ->contentGrid([
                'md' => 100,
                'xl' => 3,
            ])
            ->paginated([
                1800,
                3600,
                7200,
                'all',
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
