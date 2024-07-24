<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Enums\RegisterCourseStatus;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\VideoEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendingRelationManager extends RelationManager
{
    protected static string $relationship = 'pending';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('Course','Course')
                    ->preload()
                    ->native(false)
//                    ->hiddenOn('create')
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'Berlitz_NAME')
                    ->preload()
                    ->native(false)
                    ->required(),
                Forms\Components\ToggleButtons::make('status')
                    ->options(RegisterCourseStatus::class)
                    ->inline()
                    ->default('Not yet')
                    ->required(),
                Forms\Components\TextInput::make('Note')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Course.Course'),
                Tables\Columns\TextColumn::make('student.Berlitz_NAME')
                    ->label('Berlitz Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('Course.Starting_Date')
                    ->label('Starting Date')
                    ->badge(),
                Tables\Columns\TextColumn::make('Note')
                    ->searchable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
