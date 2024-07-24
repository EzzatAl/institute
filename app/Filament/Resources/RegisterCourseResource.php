<?php

namespace App\Filament\Resources;

use App\Enums\RegisterCourseStatus;
use App\Filament\Resources\RegisterCourseResource\Pages;
use App\Filament\Resources\RegisterCourseResource\RelationManagers;
use App\Models\RegisterCourse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RegisterCourseResource extends Resource
{
    protected static ?string $model = RegisterCourse::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('course','Course')
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

    public static function getEloquentQuery(): Builder {
        $courseId = request()->route('record');
        return parent::getEloquentQuery()->where('course_id', '=', $courseId);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Course.Course'),
                Tables\Columns\TextColumn::make('student.Berlitz_NAME')
                    ->label('Berlitz Name'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('Note')
                    ->searchable(),
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
//                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    // public static function delete(Model $record)
    // {
    //     $record->registerCourses()->delete();
    //     $record->delete();
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => CourseResource\Pages\ListRegisterCourses::route('/'),
            'create' => Pages\CreateRegisterCourse::route('/create'),
            'edit' => Pages\EditRegisterCourse::route('/{record}/edit'),
        ];
    }
}
