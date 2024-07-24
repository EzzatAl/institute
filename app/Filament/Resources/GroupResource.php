<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'Course')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'Full_name')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('classroom_id')
                    ->relationship('class_room', 'Class_Number')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('Group_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('Number_Of_Units')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\DatePicker::make('Ending_Date')
                    ->required()
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.Course'),
                Tables\Columns\TextColumn::make('employee.Full_name'),
                Tables\Columns\TextColumn::make('classroom.Class_Number'),
                Tables\Columns\TextColumn::make('Group_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Number_Of_Units')
                    ->numeric(),
                Tables\Columns\TextColumn::make('Ending_Date')
                    ->date(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
